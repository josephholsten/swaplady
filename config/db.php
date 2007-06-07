<?php
require_once 'Zend/Db.php';

$dbParams = array(
   'host' => 'localhost',
   'username' => 'root',
   'password' => ''
);

$development  = $dbParams + array('dbname' => 'swaplady_development');
$production   = $dbParams + array('dbname' => 'swaplady_production');
$test         = $dbParams + array('dbname' => 'swaplady_test');

$db = Zend_Db::factory('PDO_MYSQL', $development);
Zend_Registry::set('db', $db);

Zend_Registry::set('testDb',
	Zend_Db::factory('PDO_MYSQL', $dbParams + array('dbname' => 'swaplady_test')));
Zend_Registry::set('developmentDb', 
	Zend_Db::factory('PDO_MYSQL', $dbParams + array('dbname' => 'swaplady_development')));
Zend_Registry::set('productionDb',
	Zend_Db::factory('PDO_MYSQL', $dbParams + array('dbname' => 'swaplady_production')));