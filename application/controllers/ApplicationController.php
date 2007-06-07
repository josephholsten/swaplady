<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/View.php';
require_once 'Swaplady/Log.php';
require_once 'User.php';

class ApplicationController extends Zend_Controller_Action
{
    public function init()
    {
        // Setting up logger
        $this->logger = Zend_Registry::get('logger');
        
        $this->logger->info('Setting up database');
        $this->db = Zend_Registry::get('db');
        
        $this->logger->info('Setting up session');
        $this->session = Zend_Registry::get('session');
        
        $this->logger->info('Setting up flash');
        $this->flash = Zend_Registry::get('flash');
        
        
        $this->view = Zend_Registry::get('view');
        $this->view->session = $this->session;
        $this->view->flash = $this->flash;
        
        if (isset($this->session->user_id))
			$this->currentUserId = $this->session->user_id;
			    }
    
    public function postDispatch()
    {
        if (isset($this->flash->keep)) {
            unset($this->flash->keep);
        } else {
            $this->logger->info("flash cleared now '{$this->flash->kept}'" );
            $this->flash->unsetAll();
        }
    }
    
    
    public function ensurePost()
    {
        if ($this->_request->getMethod() != 'POST') {
            $this->logger->warn("Request method was '{$this->_request->getMethod()}'");
            $this->flash->notice = "Invalid Action";
            $this->_redirect('/');
            return;
        }
    }
    
    public function ensureLoggedIn()
    {
        if (!isset($this->session->user_id)) {
            $this->logger->notice('User isn\'t logged in');
            $this->flash->redirectedFrom = array(
                'controller' => $this->_request->getControllerName(),
                'action' => $this->_request->getActionName()
            );
            $this->flash->notice = "Please log in";
            $this->_redirect('/session/new');
        }
    }
    
    public function filterActions($function, $actions)
    {
        $trace = debug_backtrace();
        // $trace[0] is the frame of the current context
        // $trace[1] is the frame of the context we were called from
        // We want the object from the frame wew were called in
        $callingObject = $trace[1]['object'];
        if (in_array($this->_request->getActionName(), $actions)){
            call_user_func(array($callingObject, $function));
        }
    }
    
    	public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            // If the action method was not found, redirect to the new action
			$this->flash->notice = "Invalid Action";
            return $this->_redirect('/');
        }

        // all other methods throw an exception
        throw new Exception('Invalid method "' . $method . '" called');
    }
}
