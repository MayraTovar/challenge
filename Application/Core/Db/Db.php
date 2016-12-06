<?php
/*
 *  ================================================================================
 *  File:  Db.php
 *  @version 1.0.0
 *  @category
 *  @link
 *  @package
 *
 */

namespace Application\Core\Db;

use \Exception;
use \Application\Core\Parser;
use \Zend\Db\Adapter\Adapter;

/**
 * Description of Db
 *
 */
class Db extends Parser
{

    const DB_ADAPTER = "PDO_MYSQL";
    const DB_CHARSET = "utf8";

    private static $_dbAdapter = null;

    /**
     * 
     * @param string $pso_environment [Optional] Environment to connect to
     * @return \Zend\Db\Adapter\Adapter
     * @throws Exception
     */
    public static function getCurrent()
    {
        if (isset(self::$_dbAdapter)) {
            return self::$_dbAdapter;
        }

        $iniSetting = parent::INIFILE_PREFIX_DB ;

        $result = parent::getIniFileSetting($iniSetting);

        if (empty($result) || count($result) <= 0) {
            throw new Exception('Invalid or insufficient settings to make the DB connection.');
        }

        try {
            $dbAdapter = new Adapter(
                    array('driver' => self::DB_ADAPTER,
                'hostname' => $result["host"],
                'port' => $result["port"],
                'database' => $result["schema"],
                'username' => $result["user_name"],
                'options' => array(\PDO::ATTR_PERSISTENT => true),
                'persistent' => true,
                'password' => $result["user_pass"],
                'characterset' => self::DB_CHARSET,
                    )
            );

            $dbAdapter->driver->getConnection()->connect();
        } catch (Exception $e) {
            throw new Exception("DB connection couldn't be performed.");
        }
        if (false === $dbAdapter->driver->getConnection()->isConnected()) {
            throw new Exception('DB connection couldn\'t be performed.');
        }
        self::$_dbAdapter = $dbAdapter;
        return self::$_dbAdapter;
    }

    /**
     * Set DB adapter to null
     */
    public static function resetConnection()
    {
        self::$_dbAdapter = null;
    }

}