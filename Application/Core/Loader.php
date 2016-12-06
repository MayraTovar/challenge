<?php

/*
 *  ================================================================================
 * 
 *  File:  Loader.php
 *  @version 1.0.0
 *  @category 
 *  @link     
 *  @package Application/Core
 * 
 */

/**
 * Description of Parser
 *
 */

namespace Application\Core;

use Exception;

class Loader {

    const FILE_PHP = "php";
    const CLASS_NAME = 'name';
    const CLASS_DESC = 'description';
    const CLASS_PARAM = 'parameter';
    const CLASS_RETURN = 'return';
    const CLASS_PROPERTY_NAME = 'name';
    const CLASS_PROPERTY_TYPE = 'type';

    public $basePath;

    public function __construct($basePath = "") {

        if (!empty($basePath)) {
            $this->setPath($basePath);
        }
    }

    /**
     * 
     * @return string Main path for the application 
     */
    public function getPath() {
        return $this->basePath;
    }
    /**
     * 
     * @param string Set main path for the application 
     */
    public function setPath($psm_Path) {
        $this->basePath = $psm_Path;
    }

    private function _getInvalidFiles() {
        return array('.', '..', '.svn', 'core', '.git', '.md');
    }

    /**
     * 
     * @param string $directory
     * @param string $suffix
     * @param bool $recursive
     * @param array
     * @return array
     * @throws Exception
     */
    public function readDirectory($directory = "", $suffix = "", $recursive = true, $pao_avoid = array()) {
        $services = array();

        if ($handle = opendir($directory . $suffix)) {

            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $this->_getInvalidFiles())) {
                    if (is_file($directory . $suffix . $file)) {

                        $index = strrpos($file, '.');
                        $before = substr($file, 0, $index);
                        $after = substr($file, $index + 1);
                        if ($after !== self::FILE_PHP) {
                            continue;
                        }

                        $loc = "main";
                        if ($suffix != "") {
                            $loc = str_replace(DIRECTORY_SEPARATOR, '.', substr($suffix, 0, -1));
                        }

                        if (@$services[$loc] == NULL) {
                            $services[$loc] = array();
                        }
                        $services[$loc][] = array($before, $suffix);
                    } elseif (is_dir($directory . $suffix . $file) && $recursive) {
                        $insideDir = $this->readDirectory($directory, $suffix . $file . DIRECTORY_SEPARATOR, $recursive);
                        if (is_array($insideDir)) {
                            $services = $services + $insideDir;
                        }
                    } else {
                        throw new Exception("Invalid file type found: " . $directory);
                    }
                }
            }
        } else {
            throw new Exception("Invalid directory handle on " . __FILE__);
        }
        closedir($handle);
        return $services;
    }

    /**
     * Note: reflectClass() returns a Zend\Server\Reflection\Class object
     * @link http://framework.zend.com/manual/2.0/en/modules/zend.server.reflection.html Zend Doc for reflectClass explanation
     * @param string $class
     * @return Zend_Server_Reflection_Class
     */
    private function _loadClass($class) {

        if (!class_exists($class)) {
            include_once($this->basePath . $class . '.php');
        }
        return \Zend\Server\Reflection::reflectClass($class);
    }

    public function reflectClasses($pam_classes) {

        $classDefinition = array();

        foreach ($pam_classes as $folder) {
            foreach ($folder as $class) {

                if ($class[1] != '') {
                    $sub_dir = str_replace("\\", "_", $class[1]);
                    $refClass = $this->_loadClass($sub_dir . $class[0]);
                } else {
                    $refClass = $this->_loadClass($class[0]);
                }

                // Note: the reflection is held by ReflectionClass, not \Zend\Server\Reflection
                if (($refClass instanceof \Zend\Server\Reflection\ReflectionClass) == false) {
                    throw new Exception("Class couldn't be loaded in " . __FUNCTION__ . ". " . print_r($refClass));
                }

                $methods = $refClass->getMethods();

                foreach ($methods as $method) {
                    if (false === $method->isPublic()) {
                        continue;
                    }

                    $methodDefinition = array();
                    $methodDefinition[self::CLASS_NAME] = $method->getName();
                    $methodDefinition[self::CLASS_DESC] = $method->getDescription();
                    $methodDefinition[self::CLASS_PARAM] = $method->getParameters();

                    $prototypes = $method->getPrototypes();
                    $prototype = $prototypes[(sizeof($prototypes) - 1)];
                    $methodDefinition[self::CLASS_RETURN] = $prototype->getReturnType();
                    $parameters = $prototype->getParameters();

                    $parametersDefinition = array();
                    foreach ($parameters as $parameter) {
                        $parameterDefinition = array();
                        $parameterDefinition['name'] = $parameter->getName();
                        $parameterDefinition['type'] = $parameter->getType();
                        $parametersDefinition[] = $parameterDefinition;
                    }

                    $methodDefinition[self::CLASS_PARAM] = $parametersDefinition;

                    $classDefinition[$class[0]]['method'][] = $methodDefinition;
                }
            }
        }
        return $classDefinition;
    }

}