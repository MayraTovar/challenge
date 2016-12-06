<?php

/*
 *  ================================================================================
 *  File: schedule.php
 *  @version 1.0
 *  @category Cronjob
 *  @package Cronjob
 *
 */
chdir(dirname(__FILE__));
require_once ('../../bootstrap.php');

$cronjob = new \Cronjob\Classes\Schedule();
$manager = new \Cronjob\Manager($cronjob);
$manager->run();