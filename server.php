<?php

chdir(dirname(__FILE__));
require_once 'bootstrap.php';

$class = new Application\Controller\Event\Event();
if(isset($_GET['filter']) && isset($_GET['value'])) {
    print_r(json_encode($class->getbyFilter(array($_GET['filter']=>$_GET['value']))));
} else {
    print_r(json_encode($class->getWeeks()));
}
