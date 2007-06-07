<?php
require_once 'controller/BaseControllerTestCase.php';

class IndexControllerForTest extends IndexController{
	public $redirectRan = false;
	public function _redirect($url, array $options = array())
	{
		$this->redirectRan = true;
	}
}

class IndexControllerTest extends BaseControllerTestCase
{	
	public function testIndexAction() {
		$controller = new IndexControllerForTest($this->request, $this->response);
		$this->setUpGet();
		
		$controller->indexAction();
		
		$this->assertTrue($controller->redirectRan);
	}
}