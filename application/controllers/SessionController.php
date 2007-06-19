<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/View.php';
require_once 'Swaplady/Log.php';
require_once 'ApplicationController.php';
require_once 'User.php';

class SessionController extends ApplicationController
{
    public function preDispatch()
    {
        $this->filterActions('ensurePost',
            array('create', 'destroy'));
        $this->filterActions('ensureLoggedIn',
            array('destroy'));
    }
    
    public function newAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Redirect to show if already logged in');
        if (isset($this->session->user_id)) {
            $this->_redirect("user/show/{$this->session->user_id}");
        }
        
        $this->logger->info('Loading view Parameters');
        $this->view->assign(array(
            'title'    => 'Login',
            'session'  => $this->session,
            'username' => ''
        ));
        
        $this->logger->info('Rendering the new session view');
        $this->render();
        
        $this->logger->info('Clearing the flash');
        $this->flash->keep = 1;
        unset($this->flash->notice);

        $this->logger->exiting();
    }
    
    public function createAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Getting the username and password');
        $user = $this->_getParam('user');
        $username = $user['username'];
        $password = $user['password'];
        
        $this->logger->info("Loading the user by username and password '{$username}'");
        $users = new User();
        $where = $this->db->quoteInto('username = ?', $username)
               . $this->db->quoteInto('AND password = ?', $password);
        $user = $users->fetchRow($where);
        
        if (($user->username == $username) &&
            ($user->password == $password)) {
            $this->logger->info("Found the user '{$user->id}'");
            $this->session->user_id = $user->id;
            
            if(isset($this->flash->redirectedFrom)) {
                $intendedAction = $this->flash->redirectedFrom;
                $this->logger->notice("Redirecting to intended action '{$intendedAction['controller']}::{$intendedAction['action']}'");
                $this->_redirect('/' . $intendedAction['controller'] . '/' .$intendedAction['action']);
            } else {
                $this->logger->info('Redirecting to user page by default');
                $this->_redirect("user/show/{$user->id}");
            }

        } else {
            $this->flash->notice = 'Invalid username/password combination. Perhaps you\'d like to <a href="/user/new">register</a>? Or would you like us to <a href="/password/forgot">email your password to you</a>?';
            $this->flash->keep = TRUE;
            $this->_redirect('/session/new');
        }
    }
    
    public function destroyAction()
    {        
        $this->logger->entering();
        
        $this->logger->info('Removing user information from session');
        unset($this->session->user_id);
        
        $this->logger->info('Redirecting to index');
        $this->flash->notice = 'Logged Out';
        $this->_redirect('/');

        $this->logger->exiting();
    }
}
