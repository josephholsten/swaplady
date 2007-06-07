<?php
require_once 'ApplicationController.php';
require_once 'Zend/View.php';

class IndexController extends ApplicationController
{
    public function indexAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Redirect to item index');
        $this->_redirect('items');
        
        $this->logger->exiting();
    }
}
