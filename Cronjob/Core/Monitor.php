<?php
 /** 
   * @copyright © 2010 General Electric. ALL RIGHTS RESERVED.
   *
   * This file contains proprietary and GE CONFIDENTIAL Information.
   *
   * Use, disclosure or reproduction is prohibited.
   *
   * Filename: Manager.php
   * Created on Thu Aug 17 16:32:56 CDT 2010
   *
   * @author daniel.aranda@ge.com
   * @version 1.0
   */

final class Classes_CronJob_Monitor
{
   /**
    * Class Classes_CronJob_Monitor
    */
    public function  __construct()
    {

    }
    public function  __destruct()
    {
    }

    /**
     * Function getJobHistory
     *
     * Description. It sends the job information like status, start and end time, it is limit to 50 records.
     *
     * @param string $jobClass The name of the job class
     * @return obj $stmt
     */
    public function getJobHistory($jobClass)
    {
        $db = Config_Db::getConnection();
        $sql = Classes_CronJob_Queries_Monitor::getHistory();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':zend_class', $jobClass, PDO::PARAM_STR, 100);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Function viewJobActivity
     *
     * Description. It lists the information about the job execution, information from t_cronjob_history table.
     * 
     * @param int $job_run_id The job run id number
     * @return object $stmt
     */
    public function viewJobActivity($job_run_id)
    {
        $db = Config_Db::getConnection();
        $sql = Classes_CronJob_Queries_Monitor::viewJobActivity();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':cronjob_history_id', $job_run_id, PDO::PARAM_STR, 100);
        $stmt->execute();
        return $stmt->fetchObject();
    }
}