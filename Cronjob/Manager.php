<?php
/*
 *  ================================================================================
 *  @copyright(C) 2012 General Electric. ALL RIGHTS RESERVED.
 * 
 *  This file contains proprietary and GE CONFIDENTIAL Information.
 *  Use, disclosure or reproduction is prohibited.
 * 
 *  File:  Manager.php
 *  Created On: 12-Nov-2012 20:31:57
 *  @author: osvaldo.mercado <osvaldo.mercado@ge.com>
 *  @version 1.0.0
 *  @category Cronjob
 *  @link http://framework.zend.com/manual/2.0/en/modules/zend.log.overview.html     
 *  @package Cronjob
 * 
 */

namespace Cronjob;

// Include bootstrap and default settings
chdir(dirname(__FILE__));
include_once '../bootstrap.php';

use \Exception,
    Zend\Log\Logger,
    Zend\Db\Sql\Sql,
    Zend\Db\Sql\Expression,
    Zend\Db\Sql\Predicate\Operator,
    Application\Core\Db\Db,
    Application\Core\Environment\Environment,
    Application\Model\DbTable,
    Application\Model\DbGenericField,
    Application\Model\UserRoles,
    Application\Util\MailUtil,
    Application\Util\DataValidator,
    Cronjob\Model\ActivityStatus,
    Cronjob\Model\Status,
    Cronjob\Core\Core,
    Cronjob\Model\RunStatus;

/**
 * Description of Manager
 *
 * @author osvaldo.mercado
 */
class Manager extends Core
{

    public $cronjob;

    /**
     *
     * @var obj Instance of Logger for private use 
     */
    protected $_log;

    public function __construct($psm_cronjob = "")
    {
        if (!empty($psm_cronjob)) {
            $this->setCronjob($psm_cronjob);
            echo "cronjob set " . get_class($psm_cronjob) . PHP_EOL;
        }
        $this->_db = Db::getCurrent();
    }

    /**
     * Function getAllCronjobs
     *
     * Get all cronjobs
     *
     * @param string $pso_filter [Optional] The flag in case it's needed.
     */
    public function getAllCronjobs($pso_filter = "")
    {

        $sql = new Sql($this->_db);
        $select = $sql->select()
                ->from(array("tc" => DbTable::TABLE_CRONJOB))
                ->order(array("status_activity ASC", "name ASC"));

        if ($pso_filter === "VALID") {
            $select->where(array("status_activity" => ActivityStatus::IDLE,
                "status" => Status::ACTIVE));
        } elseif ($pso_filter === "IDLE") {
            $select->where(array("status_activity" => ActivityStatus::IDLE));
        } elseif ($pso_filter === "ACTIVE") {
            $select->where(array(
                new Operator(DbGenericField::STATUS, Operator::OPERATOR_NOT_EQUAL_TO, Status::INACTIVE)
            ));
        } elseif ($pso_filter === "RUNNING") {
            $select->where(array("status_activity" => ActivityStatus::RUNNING));
        }

        $stmt = $sql->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setCronjob($psm_cronjob)
    {
        $this->cronjob = $psm_cronjob;
    }

    /**
     * getServer
     * 
     * Validate if the script instance is running on a webserver(like apache).
     */
    public static function getHeader($title = 'Cron Job Manager')
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            echo '<html>
            <head>
                <title>' . $title . '</title>
                <link rel="stylesheet" href="public/assets/css/style.css" type="text/css" media="screen">
            </head>
            <body>';
            #  <div><img src="../assets/images/pr.jpg" /></div>';
        }
    }

    /**
     * Flushes memory, sets time limit & memory limit
     */
    protected static function prepareServer()
    {
        ob_implicit_flush(true);
        @ob_end_flush();
        set_time_limit(86400);
        ini_set('memory_limit', '512M');
    }

    private function _getCronjobProperty($psm_cronjob, $psm_column)
    {

        $class = str_replace("Cronjob\\Classes\\", "", get_class($psm_cronjob));

        $sql = new Sql($this->_db);
        $select = $sql->select()
                ->from(array("tc" => \Application\Model\DbTable::TABLE_CRONJOB))
                ->columns(array($psm_column))
                ->where(array("name_class" => $class))
                ->limit(1);

        $stmt = $sql->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);
    }

    private function _startLog()
    {
        // get log
        $this->_log = new \Cronjob\Core\Log();
        $this->_log->getLogger();
    }

    /**
     * public static function executeCronjob
     *
     * Executes the cronjob as well as the logging of status, activity as well as errors
     *
     */
    public function runCronjob($cronjob)
    {
        $dateTimeStart = new \DateTime();
        $dateTimeStart = $dateTimeStart->format('Y-m-d H:i:s');

        $class = str_replace("Cronjob\\Classes\\", "", get_class($cronjob));
        echo "<h2>" . $class . "</h2>";

        $cronjobActivity = $this->_getCronjobProperty($cronjob, "status_activity");
        $cronjobId = $this->_getCronjobProperty($cronjob, "cronjob_id");

        $this->_startLog();

        if (empty($cronjobActivity)) {
            $this->_log->write(\Zend\Log\Logger::CRIT, "Unable to get the status for cronjob, quitting. ");

            $emails = self::getAlertContacts(array("sso"));
            $mail = MailUtil::send($emails, $class . " cronjob unexpected runtime error   - " . Environment::getName(), "The " . $class . " cronjob suffered an unexpected runtime error:" . $this->_logText);
        }

        if ($cronjobActivity == ActivityStatus::RUNNING) {
            $this->_log->write(\Zend\Log\Logger::CRIT, 'The cronjob ' . get_class($cronjob) . ' is already running.');
            #TODO Expand current functionality so that you can unlock cronjobs without going into the DB, such as
            #Classes_Core_Log_Handler::info("<div style='float:left;background-color: red; width:200px; height: 75px;'><a href='#'>CLICK HERE TO UNLOCK</a></div>");
            return null;
        }


        // Set start time
        $this->_setTime($class, "datetime_start");
        // Set run status
        $this->_setStatusActivity($class, ActivityStatus::RUNNING);
        // Clear memory & set time/memory limits
        self::prepareServer();

        try {

            $this->_log->write(\Zend\Log\Logger::INFO, $class . " has started.");
            $timeStart = microtime(true);

            $cronjob->setLog($this->_log);
            $cronjob->run();
            $runStatus = RunStatus::SUCCESS;
            $errorLog = false;
        } catch (Exception $e) {

            $errorCode = $e->getCode();

            $this->_log->write(\Zend\Log\Logger::CRIT, "An error has ocurred in the " . $class . ' cron job.');
            $this->_log->write(\Zend\Log\Logger::CRIT, $e->getMessage());
            $this->_log->write(\Zend\Log\Logger::CRIT, "Description of error: " . $e->getPrevious());

            if ((int) $errorCode >= 0 && (int) $errorCode <= 7) {
                $code = $e->getCode();
            } else {
                $code = Logger::CRIT;
            }

            $runStatus = RunStatus::FAIL;
            $errorLog = true;

            # Send alert email
            $emails = self::getAlertContacts(array("sso"));
            $mail = MailUtil::send($emails, "{$class} Cronjob has crashed! - " . Environment::getName(), "The {$class} cronjob has crashed terribly!. The error message is: " . $e->getMessage() . ". \n"
                            . "It might also help to read this: " . $e->getPrevious());
        }

        $errorMsg = $errorLog === true ? 'with errors' : 'succesfully';
        $this->_log->write(\Zend\Log\Logger::INFO, $class . ' has finished ' . $errorMsg);

        $timeEnd = microtime(true);
        $totalTime = round(($timeEnd - $timeStart), 2);
        $this->_log->write(\Zend\Log\Logger::INFO, 'Cronjob run performed in ' . $totalTime . ' seconds.<br>');


        // Trigger alert if needed
        if (true === $this->_getAlertStatus($class, $runStatus)) {
            $this->_log->write(\Zend\Log\Logger::INFO, 'Alert sent, cronjob changed status. <br>');
            # Send alert email     
            try {
                $emails = self::getAlertContacts(array("sso"));
                $mail = MailUtil::send($emails, "{$class} Cronjob run was a {$runStatus}  - " . Environment::getName(), "The {$class} cronjob has changed status from the previous run, it is now  {$runStatus}.");
            } catch (Exception $e) {
                $this->_log->write(\Zend\Log\Logger::WARN, 'Unable to send notification email due to ' . $e->getMessage());
                $this->_log->write(\Zend\Log\Logger::INFO, Environment::getName() . "The {$class} cronjob has changed status from the previous run, it is now  {$runStatus}");
            }
        }

        // Set overall run stats
        $this->_setTime($class, "datetime_end");
        $this->_setRunStatus($class, $runStatus);
        $this->_setStatusActivity($class, ActivityStatus::IDLE);
        // Store historical log
        $dateTimeEnd = new \DateTime();
        $dateTimeEnd = $dateTimeEnd->format('Y-m-d H:i:s');
        $this->_addLog($cronjobId, $dateTimeStart, $dateTimeEnd, $this->_log->logText, $totalTime, $runStatus);
        // Destroy the log
        $this->_log = null;



        unset($cronjob);
    }

    /**
     * By default, devs need to be alerted in case anything wrong happens.
     * @todo Extend so that it's dynamic who can get alerts
     * 
     * @param array $pao_property Properties to retun
     * @return Zend\Db results
     */
    public static function getAlertContacts($pao_property)
    {
        $sql = new Sql(Db::getCurrent());
        $select = $sql->select()
                ->from(array("tu" => DbTable::TABLE_USER))
                ->where(array("tu.user_role_id" => UserRoles::DEVELOPER,
            "tu.email_alerts" => "Y",
            "tu.status" => Status::ACTIVE));

        if (DataValidator::isArrayAndNotEmpty($pao_property)) {
            $select->columns($pao_property);
        }

        $stmt = $sql->prepareStatementForSqlObject($select);
        return $stmt->execute()->getResource()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * 
     * @param int $pim_cronjobId The id of the cronjob
     * @param string $psm_timeStart Datetime start
     * @param string $psm_timeEnd Datetime end
     * @param string $log The log to be stored
     * @param string $runStatus The status of the run
     */
    private function _addLog($pim_cronjobId, $psm_timeStart, $psm_timeEnd, $log, $psm_duration, $runStatus)
    {
        $sql = new Sql($this->_db);
        $insert = $sql->insert(\Application\Model\DbTable::TABLE_LOG_CRONJOB)
                ->columns(array("cronjob_id", "date_start", "date_end", "log", "duration", "date_loaded", "status_run"))
                ->values(array($pim_cronjobId, $psm_timeStart, $psm_timeEnd, $log, $psm_duration, new Expression("NOW()"), $runStatus));
        $stmt = $sql->prepareStatementForSqlObject($insert);
        $stmt->execute();
    }

    /**
     * 
     * @param string $psm_className
     * @param string $psm_currentStatus
     * @return boolean
     */
    protected function _getAlertStatus($psm_className, $psm_currentStatus)
    {

        $alert = false;
        $sql = new Sql($this->_db);
        $select = $sql->select()
                ->from(array("tc" => DbTable::TABLE_CRONJOB))
                ->columns(array("status_run"))
                ->where(array("name_class" => $psm_className))
                ->order("status_activity ASC");

        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute()->getResource()->fetch(\PDO::FETCH_COLUMN);

        #echo PHP_EOL . $result . "the previous status";
        #echo PHP_EOL . $psm_currentStatus . " the current status";

        if ($result !== $psm_currentStatus) {
            $alert = true;
        }
        return $alert;
    }

    protected function _setStatusActivity($psm_className, $psm_status)
    {
        $sql = new Sql($this->_db);
        $update = $sql->update(DbTable::TABLE_CRONJOB)
                ->set(array("status_activity" => $psm_status)
                )
                ->where(array("name_class" => $psm_className));
        $stmt = $sql->prepareStatementForSqlObject($update);
        $stmt->execute();
    }

    protected function _setRunStatus($psm_className, $psm_status)
    {

        switch ($psm_status) {
            case \Cronjob\Model\RunStatus::FAIL:
                break;
            case \Cronjob\Model\RunStatus::SUCCESS:
                break;
            default:
                $this->_log->write(\Zend\Log\Logger::CRIT, "Invalid cronjob run status");
        }

        $sql = new Sql($this->_db);
        $update = $sql->update(\Application\Model\DbTable::TABLE_CRONJOB)
                ->set(array("status_run" => $psm_status))
                ->where(array("name_class" => $psm_className));
        $stmt = $sql->prepareStatementForSqlObject($update);
        $stmt->execute();
    }

    /**
     * 
     * @param type $psm_className The class name (stripped of \\)
     * @param type $psm_field The field to set with NOW() command
     */
    protected function _setTime($psm_className, $psm_field)
    {
        $sql = new Sql($this->_db);
        $update = $sql->update(DbTable::TABLE_CRONJOB)
                ->set(array($psm_field => new \Zend\Db\Sql\Expression("NOW()")))
                ->where(array("name_class" => $psm_className));
        $stmt = $sql->prepareStatementForSqlObject($update);
        $stmt->execute();
    }

    public function run($cronjob = "")
    {
        if (!isset($this->_log)) {
            $this->_startLog();
        }
        if (php_sapi_name() === "cli" && (isset($this->cronjob))) {
            $this->runCronjob($this->cronjob);
        } elseif (isset($_GET['cronjob']) && is_string($_GET['cronjob'])) {
            $cronjobClass = $_GET['cronjob'];
            $cronjobName = '\\Cronjob\\Classes\\' . $cronjobClass;
            if (class_exists($cronjobName)) {
                $cronjob = new $cronjobName();
                $this->runCronjob($cronjob);
            } else {
                $this->_log->write(\Zend\Log\Logger::CRIT, 'Unknown class named : ' . $cronjobName);
            }
        } else {
            $cronjobs = $this->getAllCronjobs("ACTIVE");
            $this->renderHtml($cronjobs);
        }
    }

    /**
     * renders the html visual side of the cronjob manager
     * 
     * @param array $pam_cronjobs Cronjobs data
     */
    public function renderHtml($pam_cronjobs)
    {
        self::getHeader($pageTitle = "Cronjob Manager");
        $html = '<div id="content">';
        $html .= '<h2>' . $pageTitle . '</h2>';
        $html .= '<p class="advice">Please use the run command wisely.</p>';
        $html .= '<div class="jobs-container">';

        foreach ($pam_cronjobs AS $row) {
            $html .= '<div class="jobs-warning-container">';
            $fileUrl = $this->setExecUrl($row["file"]);
            if (false === file_exists($fileUrl)) {
                $html .= '<div class="warning">This cronjob has not been setup correctly: ' . $row['file'] . '</div>';
            }
            $html .= '</div>';

            if ($row['status_activity'] == "RUNNING") {
                $datetime = new \DateTime();
                #$now = $datetime->format('Y\-m\-d\ h:i:s');
                $cronjobDatetime = new \DateTime($row['datetime_start']);
                #$cronjobTime = $cronjobDatetime->format('Y\-m\-d\ h:i:s');
                $diff = $datetime->diff($cronjobDatetime);
                # printf('%d days, %d hours, %d minutes', $diff->d, $diff->h, $diff->i);
                $html .= '
                <div class="job-running">
                    <div class="job-name">' . $row['name'] . '</div>
                    <div class="job-status">status: <strong>' . $row['status_activity'] . '</strong></div>
                    <div class="job-status">since: <strong>' . $row['datetime_start'] . '</strong></div>
                    <div class="job-status">time running: <strong>' . $diff->d . " days, " . $diff->h . " hours and " . $diff->i . " mins" . '</strong></div>
                </div>
                ';
                #TODO: add force stop button
                if (($diff->h >= 1) || ($diff->d >= 1)) { #If cronjob has been running over an hour, then probably it got stuck   
                    $emails = self::getAlertContacts(array("sso"));
                    $mail = MailUtil::send($emails, "{$row['name']} Cronjob got stuck! - " 
                   	. Environment::getName(), "The {$row['name']} cronjob has been running since {$row['datetime_start']} and is likely to be stuck. \n"
                    . "It has already passed " 
					. $diff->d 
					. " days, " 
					. $diff->h 
					. " hours and " 
					. $diff->i
                    . " mins since it began, you might want to check on it."
					);
                }
            } else {

                if ($row['status_run'] == 'FAIL') {
                    $runStatus = '<span style="color:#DF0000; font-size: 20px;">' . $row['status_run'] . '</span>';
                } else {
                    $runStatus = $row['status_run'];
                }

                $html .= '
                <div class="job">
                    <div class="job-name">' . $row['name'] . '</div>
                    <div class="job-status">Activity: <strong>' . $row['status_activity'] . '</strong></div>
                    <div class="job-status">Last Run Status: <strong>' . $runStatus . '</strong></div>
                    <div class="job-status">Last Run Time: <strong>' . $row['datetime_end'] . '</strong></div>
                    <a class="run-link" id="run"
                        href="' . $_SERVER["SCRIPT_NAME"] . '?cronjob=' . $row['name_class'] . '" target="_blank">run</a>
                </div>
                ';
                #     <a class="run-link" id="analyze"
                #      href="' . DIRECTORY_SEPARATOR . 'Executable' . DIRECTORY_SEPARATOR . 'job-analyzer.php?cronjob=' . $row['name_class'] . '" target="_blank">analyze</a>
            }
        }
        $html .= '</div>';
        $html .= '<div style="clear:both;">&nbsp;</div>';
        $html .= '</div>';
        echo $html;
    }

    public function setExecUrl($psm_file)
    {
        return APPLICATION_PATH
                . DIRECTORY_SEPARATOR
                . "Cronjob"
                . DIRECTORY_SEPARATOR
                . "Executable"
                . DIRECTORY_SEPARATOR
                . $psm_file;
    }
}
// Only run the visual Cronjob Manager if you are not running from command line
if (php_sapi_name() !== "cli") {
    $manager = new \Cronjob\Manager();
    $manager->run();
}
