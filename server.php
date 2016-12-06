<?php

header('Content-Type: application/json; charset=utf-8');
/*
* ================================================================================
 *
 * File server.php
 * @version 1.0.0
 * 
 */
require_once 'bootstrap.php';

use Application\Core\Loader,
    Application\Core\Db\Db,
    Zend\Json\Server\Cache,
    Zend\Json\Server\Server,
    Zend\Json\Server\Smd,
    Zend\Http\Request;

$server = new Server();
$server->setReturnResponse(true);
$appResponse = $server->handle();

//Validate DB
try{
    Db::getCurrent();
} catch (Exception $e) {
    $appResponse->getError()->setMessage($e->getMessage());
    echo $appResponse->getError()->getMessage();
    return;
}

//Validate Loader
try{
    $loader = new Loader();
    $loader->setPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'service/');
    $directory = $loader->readDirectory($loader->getPath());
    $services = $loader->reflectClasses($directory);

    $cacheFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'jsonrpc.cache';
} catch (Exception $e) {
    $appResponse->getError()->setMessage("An unexpected error ocurred.");
    echo $appResponse->getError()->getMessage();
    return;
}

$server = new Server();
if (false === Cache::get($cacheFile, $server)) {
    foreach ($services as $class => $method) {
        $server->setClass($class, $class);
    }
    Cache::save($cacheFile, $server);
}

if (Request::METHOD_GET == $_SERVER['REQUEST_METHOD']) {
    // Indicate the URL endpoint, and the JSON-RPC version used:
    $server->setTarget('/server.php')
            ->setEnvelope(Smd::ENV_JSONRPC_2);
    // Grab the SMD
    $smd = $server->getServiceMap();
    // Return the SMD to the client
    echo $smd;
    #echo Zend\Json\Json::prettyPrint($smd, array("indent" => " ")); #pretty print json file
    return;
} else {
    // Prevent response from triggering errors, we will handle the errors
    $server->setReturnResponse(true);
    // Handle the call

    $appResponse = $server->handle();
    if ($appResponse->isError()) {
        $appResponse->getError()->setMessage("An error has occurred: " . $appResponse->getError()->getMessage());
        $appResponse->getError()->setData("Debug info: " . PHP_EOL . trim($appResponse->getError()->getData()));
    }
    echo $appResponse;
}