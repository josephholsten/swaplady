<?php
require_once 'Zend/Db.php';
require_once 'Swaplady/Log.php';
require_once 'ApplicationController.php';
require_once 'LineItem.php';
require_once 'Item.php';

class LineitemsController extends ApplicationController
{
    public function preDispatch()
    {
/*
        $this->filterActions('ensurePost',
            array('create', 'update', 'destroy'));
*/
        $this->filterActions('ensureLoggedIn',
            array('create', 'update', 'destroy'));
    }
    
	public function createAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Building Line Item');
        $lineItem = array(
            'user_id'    => $this->session->user_id,
            'item_id'    => $this->_getParam('id'),
            'shipping'   => 1
        );
        
        
        // TODO: handle item validity with exceptions
        $this->logger->info('Ensure line item is valid');
        $validity = LineItem::isValid($lineItem);
        if (!$validity) {
            $this->logger->err('Invalid line item');
            $this->flash->notice = 'Couldn\'t add item to shopping bag';
            foreach ($lineItem['errors'] as $k => $v) {
                $this->flash->notice = $this->flash->notice . ", {$k} {$v}";
            }
            
            $this->_redirect('/transactions/new');
            
            $this->logger->info('Clearing flash notice');
            $this->flash->keep = 1;
            unset($this->flash->notice);
        } else {
            $this->logger->info('Persisting line item');
			$lineItems = new LineItem();
            $lineItem = $lineItems->createRow($lineItem);
            $lineItem->save();
        
            $this->logger->info('Redirecting to new Transaction');
            $this->_redirect("/transactions/new");
        }
        
        $this->logger->exiting();
    }
    
    public function updateAction()
    {
    	$this->logger->entering();
    	
    	$this->logger->info('Loading line item from database');
    	$lineItems = new LineItem();
    	$lineItem = $lineItems->find($this->_getParam('id'))->current();
    	
    	$this->logger->info('Updating from post params');
    	$lineItem->shipping = $this->_getParam('shipping');
    	
    	$this->logger->info('Saving to databse');
    	$lineItem->save();
    	
    	$this->logger->info('Redirecting to new transaction');
    	$this->_redirect('/transactions/new');
    	
    	$this->logger->exiting();
    }
    
    public function destroyAction()
    {
        $this->logger->entering();
        
        try {        
			$this->logger->info('Getting item from Params');
			$lineItems = new LineItem();
            $lineItem = $lineItems->find($this->_getParam('id'))->current();
            
			$this->logger->info('Checking Item is in owned by user');
        	if ($lineItem->user_id != $this->session->user_id)
        		throw new Exception("Line Item:{$lineItem->id} is not owned by user:{$this->session->user_id}");
            
            $this->logger->info('Deleting item from bag');
            $lineItem->delete();
            
            $this->logger->info('Redirecting to index');
            $this->_redirect('/');
        } catch (Exception $e) {
        
            $this->logger->warn($e->getMessage());
            $this->flash->notice = 'Invalid Action';
            $this->_redirect('/');
            
            $this->logger->info('Clearing flash notice');
            $this->flash->keep = 1;
            unset($this->flash->notice);
        }
        
        $this->logger->exiting();
    }
}