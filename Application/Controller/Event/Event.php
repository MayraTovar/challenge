<?php
/*
 *  ================================================================================
 *  File:  Event.php
 *  @version 1.0.0
 *  @category
 *  @link
 *  @package
 *
 */
namespace Application\Controller\Event;

use Application\Core\Db\Db;
use Application\Model\DbTable;
use Application\Controller\Team\Team;
use Zend\Db\Sql\Sql;

class Event {

    function __construct() {
        
    }
    public function getbyFilter($pam_filter) {
        $response = array();
        $db = new Sql(Db::getCurrent());
        $select = $db->select()->from(array('e' => DbTable::TABLE_EVENT))
                ->where($pam_filter);
        $stmt = $db->prepareStatementForSqlObject($select);
        $result = $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
        if(!empty($result)) {
            foreach ($result as $value) {
                $teamHomeData = Team::getbyId($value['home_team_id']);
                $teamAwayData = Team::getbyId($value['away_team_id']);
                $value['HomeTeamName'] = $teamHomeData[0]['name'];
                $value['HomeTeamKey'] = $teamHomeData[0]['abbreviation'];
                $value['AwayTeamName'] = $teamAwayData[0]['name'];
                $value['AwayTeamKey'] = $teamAwayData[0]['abbreviation'];
                $response[] = $value;
            }
        }
        return $response;
    }

}

