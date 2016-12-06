<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
chdir(dirname(__FILE__));
require_once ('../bootstrap.php');

$class = new Application\Controller\Event\Event();
Zend\Debug\Debug::dump($class->getbyFilter(array('date' => '2016-12-03')));
