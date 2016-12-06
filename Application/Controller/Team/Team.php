<?php
/*
 *  ================================================================================
 *  File:  Team.php
 *  @version 1.0.0
 *  @category
 *  @link
 *  @package
 *
 */
namespace Application\Controller\Team;

use Application\Core\Db\Db;
use Application\Model\DbTable;
use Zend\Db\Sql\Sql;

class Team {

    function __construct() {
        
    }
    public function getAll() {
        $db = new Sql(Db::getCurrent());
        $select = $db->select()->from(array('t' => DbTable::TABLE_TEAM));
        $stmt = $db->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }
    public static function getbyId($pim_teamID) {
        $db = new Sql(Db::getCurrent());
        $select = $db->select()->from(array('t' => DbTable::TABLE_TEAM))
                ->where(array('t.teamId' => $pim_teamID));
        $stmt = $db->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

}

