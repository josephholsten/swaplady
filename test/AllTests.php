<?php
/*
 * Test Dispatch
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

require_once('controller/ConversationsControllerTest.php');
require_once('controller/ImageControllerTest.php');
require_once('controller/IndexControllerTest.php');
require_once('controller/ItemsControllerTest.php');
require_once('controller/LineItemsControllerTest.php');
require_once('controller/MessageControllerTest.php');
require_once('controller/PasswordControllerTest.php');
require_once('controller/SearchControllerTest.php');
require_once('controller/SessionControllerTest.php');
require_once('controller/TagControllerTest.php');
require_once('controller/TransactionsControllerTest.php');
require_once('controller/UserControllerTest.php');

class AllTests{
	public static function main(){
		PHPUnit_TextUI_TestRunner::run(self::suite(),array());
	}
	public static function suite(){
		$suite = new PHPUnit_Framework_TestSuite();
		$suite->setName('swaplady');
		$suite->addTestSuite('ConversationsControllerTest');
		$suite->addTestSuite('ImageControllerTest');
		$suite->addTestSuite('IndexControllerTest');
		$suite->addTestSuite('ItemsControllerTest');
		$suite->addTestSuite('LineItemsControllerTest');
		$suite->addTestSuite('MessageControllerTest');
		$suite->addTestSuite('PasswordControllerTest');
		$suite->addTestSuite('SearchControllerTest');
		$suite->addTestSuite('SessionControllerTest');
		$suite->addTestSuite('TagControllerTest');
		$suite->addTestSuite('TransactionsControllerTest');
		$suite->addTestSuite('UserControllerTest');
		return $suite;
	}
}

AllTests::main();