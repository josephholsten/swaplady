<?php

class BaseControllerTestCase extends PHPUnit_Framework_TestCase
{
	protected $request;
	protected $response;
	
	protected function setUp(){
		$_GET = array();
		$_POST = array();
		$this->response = $this->makeResponse();
		$this->request = $this->makeRequest();
	}
	
	protected function makeRequest($url = null){
		return new Zend_Controller_Request_Http($url);
	}
	
	protected function makeResponse(){
		return new Zend_Controller_Response_Http();
	}
	
	protected function setUpPost(array $params = array()){
		$_SERVER['REQUEST_METHOD'] = 'POST';
		foreach($params as $key=>$value){
			$_POST[$key] = $value;
		}
	}
	
	protected function setUpGet(array $params = array()){
		$_SERVER['REQUEST_METHOD'] = 'GET';
		foreach($params as $key=>$value){
			$_GET[$key] = $value;
		}
	}
	
	/* Standard Actions
	 * index new create show edit update destroy
	 * 
	 * public function testIndexAction() {}
	 * public function testNewAction() {}
	 * public function testCreateAction() {}
	 * public function testShowAction() {}
	 * public function testEditAction() {}
	 * public function testUpdateAction() {}
	 * public function testDestroyAction() {}
	 */
	 
	 

}