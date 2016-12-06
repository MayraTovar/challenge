<?php

/*
 *  ================================================================================
 *  File:  CronjobInterface.php
 *  @version 1.0.0
 *  @category Interface
 *  @link     
 *  @package Cronjob\IFace
 * 
 */

namespace Cronjob\IFace;

interface CronjobInterface {

    /**
     * Function run
     * Description. Method that will start the execution of the job
     *
     * @return void
     * @throws \Exception
     */
    public function run();

    /**
     * Function setLog
     * Inherits the log object from the manager for customized logging of the cronjob (manager provides generic logging).
     * In the child class, this function MUST run the AbstractCronjob setLog class.
     * 
     * @param obj $log
     */
    public function setLog($log);
}
