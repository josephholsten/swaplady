<?php
require_once 'controller/BaseControllerTestCase.php';

class UserControllerForTest extends UserController
{
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

class UserControllerTest extends BaseControllerTestCase
{

	public function testNewAction() {}
	public function testCreateAction() {}
	public function testShowAction() {}
	public function testEditAction() {}
	public function testUpdateAction() {}
	public function testDestroyAction() {}
	
/*
	public function testIndexAction_AssignsVarsAndRenders(){
		$indexController = new IndexControllerForTest($this->request, $this->response);
		$this->setUpGet();
		
		$indexController->indexAction();
		
		$viewVars = $indexController->view->getVars();
		$this->assertEquals("My Albums",$viewVars['title']);
		$albums = $viewVars['albums'];
		$this->assertTrue($albums instanceof Zend_Db_Table_Rowset);
		$this->assertEquals(2,$albums->count());
		$this->assertTrue($indexController->renderRan);
	}

*/	
/*
	public function testAddAction_Get_AssignsFormVarsAndRenders(){
		$indexController = new IndexControllerForTest($this->request,$this->response);
		$this->setUpGet();

		$indexController->addAction();

		$viewVars = $indexController->view->getVars();
		$this->assertEquals('Add New Album',$viewVars['title']);
		$this->assertEquals('add',$viewVars['action']);
		$this->assertEquals('Add',$viewVars['buttonText']);
		$this->assertTrue($indexController->renderRan);
	}

*/	
/*
	public function testAddAction_PostWithParams_RunsInsertAndRedirects(){
		$indexController = new IndexControllerForTest($this->request,$this->response);

		$this->setUpPost(array('artist'=>'phish','title'=>'hoist','add'=>'Add'));
                
		$mockAlbum = $this->getMock('Album',array('insert'));
		$mockAlbum->expects($this->once())
				->method('insert')
				->with(array('artist'=>'phish','title'=>'hoist'));

		$indexController->setAlbum($mockAlbum);
		$indexController->addAction();

		$this->assertTrue($indexController->redirectRan);
	}
*/
}