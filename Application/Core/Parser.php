<?php

/*
 *  ================================================================================
 *  File:  Parser.php
 *  @version 1.0.0
 *  @category
 *  @link
 *  @package Application\Core
 *
 */

namespace Application\Core;

use Exception;

/**
 * Parse the application.ini file and return all of the contents as array
 *
 */
class Parser {

    const INIFILE_PREFIX_DB = "DB_LOCALHOST";
    const INIFILE_PREFIX_ENV = "ENV_LOCALHOST";
    const INIFILE_SETTING_VALUE = "VALUE";
    const INIFILE_SETTING_KEY = "KEY";

    private static function _getIniFile() {
        $data = parse_ini_file(APPLICATION_PATH
                . DIRECTORY_SEPARATOR . 'Application'
                . DIRECTORY_SEPARATOR . 'Config'
                . DIRECTORY_SEPARATOR . 'application.ini', true);
        if (is_array($data)) {
            return $data;
        } else {
            throw new Exception("Error reading ini file.");
        }
    }

    protected static function getIniFileSetting($psm_setting) {
        $iniSetting = null;

        $iniFile = self::_getIniFile();

        reset($iniFile);

        for ($i = 0; $i < count($iniFile); $i++) {
            foreach ($iniFile[key($iniFile)] as $key => $value) {

                // First look if the key is the setting (returns array)
                if(key($iniFile) == $psm_setting) {
                    return $iniFile[key($iniFile)];
                }

                // Look for a single value (returns string)
                if (is_array($value) && in_array($psm_setting, $value)) {
                    $explode = explode('_', key($iniFile));
                    if (!isset($explode[1]) || !is_string($explode[1])) {
                        throw new Exception("Invalid setting found: " . $explode[1]);
                    }
                    $iniSetting = $explode[1];
                }
            }
            next($iniFile);
        }

        if(empty($iniSetting)) {
              throw new Exception("Setting not found in ini file: "  . $psm_setting);
        }

        return strtoupper($iniSetting);
    }

}