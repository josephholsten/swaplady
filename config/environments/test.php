<?php
require_once 'Zend/View.php';
require_once 'Zend/Log.php';
require_once 'Swaplady/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Session.php';
require_once 'db.php';
require_once 'routes.php';

// set timezone
date_default_timezone_set('US/Central');
// base url
Zend_Registry::set('baseUrl', '/');

// session
Zend_Session::setOptions(array(
    'name'             => "Swaplady",
    'save_path'        => '../tmp/sessions',
    'use_only_cookies' => 'on'
));
$session = new Zend_Session_Namespace();
Zend_Registry::set('session', $session);
$flash = new Zend_Session_Namespace('flash');
Zend_Registry::set('flash', $flash);

// views path
$view = new Zend_View();
$view->setScriptPath('../application/views');
Zend_Registry::set('view', $view);

// action controller path
Zend_Registry::set('actionController',
                // feel free to use an array here
               '../application/controllers');

$logger = new Swaplady_Log(new Zend_Log_Writer_Stream('../log/development.txt'));
Zend_Registry::set('logger', $logger);

// Set logging level
/* $this->logger->debug(setLevel($this->logger->debug(LEVEL_DEBUG); */

Zend_Registry::set('env', 'development');

