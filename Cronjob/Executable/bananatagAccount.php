<?php

/*
 *  ================================================================================
 *  @copyright(C) 2015 General Electric. ALL RIGHTS RESERVED.
 *
 *  This file contains proprietary and GE CONFIDENTIAL Information.
 *  Use, disclosure or reproduction is prohibited.
 *
 *  File: bananatagAccount.php
 *  Created On: 2015/09/04
 *  @author: mayra.tovar@ge.com
 *  @version 1.0
 *  @category Cronjob
 *  @package Cronjob
 *
 */
chdir(dirname(__FILE__));
require_once ('../../bootstrap.php');

$cronjob = new \Cronjob\Classes\BananatagAccount();
$manager = new \Cronjob\Manager($cronjob);
$manager->run();