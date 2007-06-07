<?php
require_once 'controller/BaseControllerTestCase.php';

class PasswordControllerForTest extends PasswordController{
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

class PasswordControllerTest extends BaseControllerTestCase
{	
	public function testForgotAction() {}
	public function testSendAction() {}
}