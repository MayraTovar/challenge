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

class Team extends AbstractCronjob implements CronjobInterface {
    /**
     * API constants details
     */
    const REQUEST_URI = 'https://api.fantasydata.net/v3/cfb/scores/JSON';
    const TEAM_SERVICE = '/Teams';
    const API_HEADERS = array('Content-Type: application/json','Ocp-Apim-Subscription-Key: 76f56dc7c37742028ae425dd518c3ed6');
    
    public function run() {
        $this->_loadTeams();
    }
    public function setLog($log) {
        parent::setLog($log);
    }
    private function _loadTeams() {
        $data = self::__doRequest(self::TEAM_SERVICE);
        $result = array();
        echo 'Inserting data'.PHP_EOL;
        $dbTable = DbTable::TABLE_TEAM;
        $columns = array('teamId', 'abbreviation', 'name', 'school');
        foreach ($data as $value) {
            if(isset($value['TeamID'])) {
                $valuesData['teamId'] = $value['TeamID'];
                $valuesData['abbreviation'] = $value['Key'];
                $valuesData['name'] = $value['Name'];
                $valuesData['school'] = $value['School'];
                $result[] = $valuesData;
            }
        }
        if (!empty($result)){
            $this->_saveData($result, $dbTable, $columns);
            return true;
        }
        echo 'Event has been inserted';
    }
    
    private static function __doRequest($psm_service) {
        $response = RequestUtil::getContents(self::REQUEST_URI.$psm_service, self::API_HEADERS);
        return json_decode($response,true);
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