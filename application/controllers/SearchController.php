<?php
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Search/Lucene.php';
require_once 'ApplicationController.php';
require_once 'Swaplady/Log.php';
require_once 'Item.php';
require_once 'ItemIndex.php';


class SearchController extends ApplicationController
{
    public function newAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Populating view parameters');
        $this->view->assign(array(
            'title' => 'Search',
            'view'  => 'searchNew.phtml'
        ));
        
        $this->logger->info('Rendering application template');
        echo $this->view->render('applicationTemplate.phtml');
    }
    
    public function showAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Redirect if this is a query');
        if ($this->getRequest()->getQuery('q')) {
            $this->_redirect("/search/{$this->getRequest()->getQuery('q')}");
        }
        
        $this->logger->info('Get query from params');
        $query = $this->_getParam('q');
        
        $this->logger->info('Query item index');
        $items = ItemIndex::find($query);
        
        $this->logger->info('Populating view parameters');
        $this->view->assign(array(
            'title' => 'Search Results',
            'view'  => 'searchShow.phtml',
            'items' => $items
        ));
        
        $this->logger->info('Rendering application template');
        echo $this->view->render('applicationTemplate.phtml');
        
        $this->logger->exiting();
    }
    
    public function buildAction()
    {
        $this->logger->entering();
		
		$this->logger->info('Rebuilding item index');		
		ItemIndex::rebuild();

		$this->logger->info('Redirecting to new search');
        $this->_redirect('/search/');
        
        $this->logger->exiting();
    }
}
