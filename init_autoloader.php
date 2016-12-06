<?php
/*
 * ================================================================================
 *
 * File init_autolader.php
 * @version 1.0.0
 * 
 * @category Zend
 *  @link    http://framework.zend.com/manual/2.0/en/modules/zend.loader.standard-autoloader.html 
 * @package Autoloader
 * 
 * ================================================================================
 * 
 */

// Autoloading class 
require_once 'Zend'
        . DIRECTORY_SEPARATOR
        . 'Loader'
        . DIRECTORY_SEPARATOR
        . 'StandardAutoloader.php';

use Zend\Loader\StandardAutoloader;

$loader = new StandardAutoloader();
// Set to true so that  the class will register the “Zend” namespace to the directory above where its own classfile is located on the filesystem.
$loader->setOptions(array(StandardAutoloader::AUTOREGISTER_ZF => true))
        ->register();

// Register the folders to use by namespace, Application holds all of the classes for the app, add any other below
// Note: Unix is case sensitive for the below
$loader->registerNamespaces(array(
    'Application' => __DIR__ . DIRECTORY_SEPARATOR . 'Application',
    'Cronjob' => __DIR__ . DIRECTORY_SEPARATOR . 'Cronjob'
));

// Register with spl_autoload:
$loader->register();