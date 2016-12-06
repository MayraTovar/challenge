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
                $value['HomeTeamName'] = $teamHomeData['name'];
                $value['HomeTeamKey'] = $teamHomeData['abbreviation'];
                $value['AwayTeamName'] = $teamAwayData['name'];
                $value['AwayTeamKey'] = $teamAwayData['abbreviation'];
                $response[] = $value;
            }
        }
        return $response;
    }

    public function getWeeks() {
        $db = new Sql(Db::getCurrent());
        $select = $db->select()->from(array('e' => DbTable::TABLE_EVENT))
                ->columns(array('week'))
                ->group(array('week'));
        $stmt = $db->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_COLUMN);
    }

}
