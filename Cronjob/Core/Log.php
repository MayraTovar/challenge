<?php

/*
 *  ================================================================================
 *  @copyright(C) 2012 General Electric. ALL RIGHTS RESERVED.
 * 
 *  This file contains proprietary and GE CONFIDENTIAL Information.
 *  Use, disclosure or reproduction is prohibited.
 * 
 *  File:  Log.php
 *  Created On: 19-Dec-2012 11:25:19
 *  @author: osvaldo.mercado <osvaldo.mercado@ge.com>
 *  @version 1.0.0
 *  @category 
 *  @link     
 *  @package 
 * 
 */

namespace Cronjob\Core;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use DateTime;

/**
 * Description of Log
 *
 * @author osvaldo.mercado
 */
class Log extends Logger {

    const LOG_OUTPUT = 'php://output';

    public $logText;
    public $logger;
    public $writer;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 
     * @param string $pso_stream [Optional] Parameter to specify the output log
     * 
     * @link http://tools.ietf.org/html/rfc3164 Agreed format for logs: RFC3164. The defaut used by ZF2
     */
    public function getLogger($pso_stream = self::LOG_OUTPUT) {
        $this->logger = new Logger();
        $this->writer = new Stream($pso_stream);
        $this->logger->addWriter($this->writer);
        $this->writer->setLogSeparator("<br>");
    }

    public function write($psm_priority, $psm_msg) {
        // Execute the normal log
        $this->logger->log($psm_priority, $psm_msg);
        // Fake the log for DB storage
        $timestamp = new DateTime();
        // Change format to readable
        $testing = $timestamp->format('Y-m-d H:i:s');
        // Get  name for log priority
        $errorName = $this->priorities[$psm_priority];
        // Store entire log as a string to store it
        $this->logText .= $testing . " " . $errorName . "  " . $psm_msg . PHP_EOL;
    }

}
