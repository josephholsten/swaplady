<?php
require_once 'controller/BaseControllerTestCase.php';

class SessionControllerForTest extends SessionController{
	public $renderRan = false;
	public $redirectRan = false;

	public function initView(){
		$this->view = new Zend_View();
	}

	public function render($action = null, $name = null, $noController = false)
	{
		$this->renderRan = true;
	}
	public function _redirect($url, array $options = array())
	{
		$this->redirectRan = true;
	}
}

class SessionControllerTest extends BaseControllerTestCase
{
	public function testNewAction() {
		$controller = new SesstionControllerForTest($this->request, $this->response);
		$this->setUpGet();
		
		$controller->newAction();
		
		$this->assertTrue($controller->renderRan);
	}
	public function testCreateAction() {}
	public function testDestroyAction() {}
	
}