<?php
require_once 'controller/BaseControllerTestCase.php';

class ImageControllerForTest extends ImageController{
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

class ImageControllerTest extends BaseControllerTestCase
{
	public function testNewAction() {
		$controller = new ImageControllerForTest($this->request, $this->response);
		$this->setUpGet();
		
		$controller->newAction();
		
/* 		$viewVars = $this->view->getVars(); */
/* 		$this->assertEquals('New image', $viewVars['title']); */
		$this->assertTrue($controller->renderRan);
	}
	
	public function testCreateAction() {}
	public function testShowAction() {}
	public function testDestroyAction() {}
	
}