<?php

/*
 *  ================================================================================
 *  File: Schedule.php
 *  @version 1.0
 *  @category Cronjob
 *  @package Cronjob
 *
 */
namespace Cronjob\Classes;

use Application\Core\Db\Db,
    Application\Model\DbTable,
    Cronjob\Util\RequestUtil,
    Cronjob\Core\AbstractCronjob,
    Cronjob\IFace\CronjobInterface,
    Zend\Db\Metadata\Metadata;

class Schedule extends AbstractCronjob implements CronjobInterface {
    /**
     * Indicates wheter the data should be collected historically
     */
    private $isHistorical = false;
    /**
     * API constants details
     */
    const REQUEST_URI = 'https://api.fantasydata.net/v3/cfb/scores/JSON';
    const GAME_SERVICE = '/Games/2016';
    const API_HEADERS = array('Content-Type: application/json','Ocp-Apim-Subscription-Key: 76f56dc7c37742028ae425dd518c3ed6');
    
    public function run() {
        if(isset($_GET['historicalMode']) && $_GET['historicalMode'] == true){
            $this->isHistorical = true;
        }
        $this->_loadFootballSchedule();
    }
    public function setLog($log) {
        parent::setLog($log);
    }
    private function _loadFootballSchedule() {
        $data = self::__doRequest(self::GAME_SERVICE);
        $result = array();
        foreach ($data as $value) {
            if(isset($value['Updated']) && $this->isHistorical == false) {
                $value['Updated'] = date('Y-m-d', strtotime($value['Updated']));
                $today = date('Y-m-d', strtotime("-1 days"));
                //echo $today.PHP_EOL.$value['Updated'];
                if($value['Updated'] < $today){
                    continue;
                }
            }
            $result[] = $value;
        }
        if(!empty($result)) {
            $this->_insertEvent($result);
        }
    }
    
    private static function __doRequest($psm_service) {
        $response = RequestUtil::getContents(self::REQUEST_URI.$psm_service, self::API_HEADERS);
        return json_decode($response,true);
    }

    private function _insertEvent($pam_data) {
        echo 'Inserting data'.PHP_EOL;
        $dbTable = DbTable::TABLE_EVENT;
        $data = array();
        $columns = array('eventId', 'date', 'home_team_id', 'away_team_id', 'home_score', 'away_score', 'location', 'week', 'status');
        foreach ($pam_data as $value) {
            if (isset($value['GameID'])) {
                $dateFormated = date('Y-m-d H:i:s', strtotime($value['DateTime']));
                $stadium = $value['Stadium']['Name'];
                $valuesData['eventId'] = $value['GameID'];
                $valuesData['date'] = $dateFormated;
                $valuesData['home_team_id'] = $value['HomeTeamID'];
                $valuesData['away_team_id'] = $value['AwayTeamID'];
                $valuesData['home_score'] = $value['HomeTeamScore'];
                $valuesData['away_score'] = $value['AwayTeamScore'];
                $valuesData['location'] = $stadium;
                $valuesData['week'] = $value['Week'];
                $valuesData['status'] = $value['Status'];
                $data[] = $valuesData;
            }
        }
        if (!empty($data)){
            $this->_saveData($data, $dbTable, $columns);
            return true;
        }
        echo 'Event has been inserted';
    }
    private function _saveData($pam_data, $psm_dbTable, $pam_columns = array()) {
        $db = Db::getCurrent();
        $sql = new Metadata($db);
        $columnsArray = $pam_columns;
        if (empty($pam_columns)) {
            $columnsArray = $sql->getColumnNames($psm_dbTable);
        }
        $columns = ("(".  implode(',', $columnsArray).")");
        $columnsCount = count($columnsArray);
        $placeholder = "(" . implode(',', array_fill(0, $columnsCount, '?')) . ")";
        $placeholder = implode(',', array_fill(0, count($pam_data), $placeholder));
        $values = array();
        foreach ($pam_data as $row) {
            foreach ($row as $value) {
                $values[] = $value;
            }
        }
        try {
            $replace = "REPLACE INTO $psm_dbTable $columns VALUES $placeholder";
            $db->query($replace)->execute($values);
        } catch (\Exception $exc) {
            echo $exc->getPrevious();
            return false;
        }
        return true;
    }
}