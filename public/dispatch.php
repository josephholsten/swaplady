<?php
/*
 * Front Controller Dispatch
 * 
 * Don't change this file. Most modifications are made in
 * config/environment.php and elsewhere in config/
 * 
 */

// Set up include path to use library and model classes
set_include_path('.' . PATH_SEPARATOR
               . '../library' . PATH_SEPARATOR
               . '../application/models'. PATH_SEPARATOR
               . '../application/controllers'. PATH_SEPARATOR
               . get_include_path());

include "Zend/Loader.php";
spl_autoload_register(array('Zend_Loader', 'autoload'));

require_once '../config/environment.php';

Zend_Db_Table::setDefaultAdapter(Zend_Registry::get('db'));

$frontController = Zend_Controller_Front::getInstance();
$frontController->setControllerDirectory(Zend_Registry::get('actionController'));
$frontController->setRouter(Zend_Registry::get('router'));
$frontController->setBaseUrl(Zend_Registry::get('baseUrl'));
if (Zend_Registry::get('env') == 'development') {
    $frontController->throwExceptions(true);
}

$response = $frontController->dispatch();