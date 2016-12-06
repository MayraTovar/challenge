<?php

/*
 * ================================================================================
 *
 * File bootstrap.php
 * @version 1.0.0
 *
 * @category Zend
 *  @link
 * @package Bootstrap
 * ================================================================================
 */
set_time_limit(0);
// Recommended settings for development
error_reporting(E_ALL | E_STRICT);
// Display all errors
ini_set('display_errors', '1');
// Make everything is relative to the application root now.
chdir(dirname(__DIR__));
// Ensure library/ is on include_path
set_include_path(
        dirname(__FILE__)
        . DIRECTORY_SEPARATOR
        . 'library'
        . DIRECTORY_SEPARATOR
        . PATH_SEPARATOR .
        get_include_path()
);

session_start();
ob_start();
define("APPLICATION_PATH", __DIR__);

require 'init_autoloader.php';