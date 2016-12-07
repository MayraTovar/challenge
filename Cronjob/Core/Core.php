<?php

/*
 *  ================================================================================
 *  File:  Core.php
 *  @version 1.0.0
 *  @category Core
 *  @link     
 *  @package Cronjob\Core
 * 
 */

namespace Cronjob\Core;

/**
 * Description of Core
 *
 */
class Core {

    protected $_path;
    protected $writer;
    protected $_logText = "";
    public $log;

    public function logData($psm_msg) {
        $this->_logText .= $psm_msg . PHP_EOL;
    }

    public function setPath($path) {
        $this->_path = $path;
    }

    public function getPath() {
        return $this->_path;
    }

    public function runCliCronjob($psm_cronjob) {
        $command = "php  " . $this->_path . DIRECTORY_SEPARATOR . $psm_cronjob;
        $shell = 'nohup ' . $command . ' > /dev/null & echo $!';
        $output = shell_exec($shell);
        return $output;
    }

    public function isTimeToRun($frequency) {

        $items = explode(' ', trim($frequency));

        if (count($items) != 5) {
            echo "Unexpected frequency value: {$frequency} ";
            return false;
        }

        //Validate weekday, 1 for monday, 7 for sunday WITHOUT leading zeros
        if (!self::validateItem($items[4], date('N'))) {
            return false;
        }
        //Validate month, 1 for january, 12 for december WITHOUT leading zeros
        if (!self::validateItem($items[3], date('m'))) {
            return false;
        }
        //Validate day, WITHOUT leading zeros
        if (!self::validateItem($items[2], date('d'))) {
            return false;
        }
        //Validate hour, WITHOUT leading zeros
        if (!self::validateItem($items[1], date('G'))) {
            return false;
        }
        //Validate minute, WITH leading zeros
        if (!self::validateItem($items[0], date('i'))) {
            return false;
        }

        return true;
    }

    public static function validateItem($value, $compare) {
        if ($value == '*') {
            return true;
        }
        if (strpos($value, '*/') === 0) {
            $number = (int) substr($value, 2);
            if ($number > 0) {
                $compare = (int) $compare;
                $result = $compare % $number;
                $result = (int) $result;
                if ($result === 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        if (in_array($compare, explode(',', $value))) {
            return true;
        }
        return false;
    }

}

