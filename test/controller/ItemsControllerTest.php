<?php
require_once 'controller/BaseControllerTestCase.php';

class ItemsControllerForTest extends ItemsController{
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

class ItemsControllerTest extends BaseControllerTestCase
{	
	public function testIndexAction() {}
	public function testNewAction() {}
	public function testCreateAction() {}
	public function testShowAction() {}
	public function testEditAction() {}
	public function testUpdateAction() {}
	public function testDestroyAction() {}
	
}