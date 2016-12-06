<?php

/*
 *  ================================================================================
 *  File:  AbstractCronjob.php
 *  @version 1.0.0
 *  @category 
 *  @link     
 *  @package 
 * 
 */

namespace Cronjob\Core;

abstract class AbstractCronjob {

    protected $_log;

    protected function setLog($pom_log) {
        if (empty($pom_log)) {
            throw new Exception("Invalid log object");
        }
        $this->_log = $pom_log;
    }

}

