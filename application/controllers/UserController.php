<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Mail.php';
require_once 'Zend/View.php';
require_once 'Swaplady/Log.php';
require_once 'ApplicationController.php';
require_once 'User.php';
require_once 'Item.php';
require_once 'Transaction.php';

/*
 * User Controller
 * Handle the creation (registration), updating, deletion,
 * and showing of users
 */
class UserController extends ApplicationController
{
	var $states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'AA' => 'Armed Forces Americas',
		'AE' => 'Armed Forces Europe',
		'AP' => 'Armed Forces Pacific',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',		
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'International' => 'International'
	);

    public function preDispatch()
    {
        $this->filterActions('ensurePost',
            array('create', 'update', 'destroy'));
        $this->filterActions('ensureLoggedIn',
            array('edit', 'update', 'destroy'));
    }
    
    public function newAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Building a new user');
        $users = new User();
        $user = $users->fetchNew();
        
        $this->logger->info('Loading view parameters');
        $this->view->assign(array(
            'title'    => "New User",
            'user'     => $user->toArray() + array('password_confirm' => '', 'emailConfirm' => ''),
            'states'   => $this->states,
            'terms'    => 0
        ));
        
        $this->logger->info('Render view');
        echo $this->render();

        $this->logger->info('Clearing flash notice');
        $this->flash->keep = 1;
        unset($this->flash->notice);
        
        $this->logger->exiting();
    }
    
    public function createAction()
    {
        $this->logger->entering();
        
        $paramTerms = $this->_getParam('terms');
        $paramUser = $this->_getParam('user');
        
        $this->logger->info('Validating user from params');
        $validity = User::isValid($paramUser);
        if ($paramTerms != 1 ||
            !$validity) {
            $this->logger->notice('Invalid User');
            
            $this->flash->notice = "Please complete all fields and agree to the terms and conditions";
            if (!$validity) {
                foreach ($paramUser['errors'] as $k => $v) {
                    $this->flash->notice = $this->flash->notice . ", {$k} {$v}";
                }
            }
            
            $this->logger->info('Loading view parameters');
            $this->view->assign(array(
                'title'    => "New User",
/*                 'view'     => 'userNew.phtml', */
                'user'     => $paramUser,
                'states'   => $this->states,
                'terms'    => $paramTerms
            ));
            
            $this->logger->info('Render view');
/*             echo $this->view->render('applicationTemplate.phtml'); */
			$this->render();

            $this->logger->info('Clearing flash notice');
            $this->flash->keep = 1;
            unset($this->flash->notice);
        } else {
            $this->logger->info('Building a new user from request');
            $users = new User();
            $user = $users->fetchNew();
            $paramUser = $users->filterColumns($paramUser);
            $user->setFromArray($paramUser);

            $this->logger->info('Inserting the user');
            if ($user->save()) {
                $this->logger->notice('Crediting signup bonus');
                $transactions = new Transaction();
                $transactions->signupUser($user);
                
                $this->logger->notice('Sending welcome message');
                $mail = new Zend_Mail();
                $mail->setBodyText('Welcome to swaplady.');
                $mail->setFrom('somebody@example.com', 'Some Sender');
                $mail->addTo($user->email, $user->name);
                $mail->setSubject('Welcome to Swaplady');
                $mail->send();
            
                $this->logger->notice('Marking as logged in');
                $this->session->user_id = $user->id;

                if(isset($this->flash->redirectedFrom)) {
                    $intendedAction = $this->flash->redirectedFrom;
                    $this->logger->info("Redirecting to intended action '{$intendedAction['controller']}::{$intendedAction['action']}'");
                    $this->_redirect('/' . $intendedAction['controller'] . '/' .$intendedAction['action']);
                } else {
                    $this->logger->info("Redirecting to show user {$user->id}");
                    $this->_redirect("user/show/{$user->id}");
                }

            }
        }
        $this->logger->exiting();
    }
    
    public function showAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Getting the id');
        $id = $this->_getParam('id');
        
        $this->logger->notice("Loading the user by id: '{$id}'");
        $users = new User();
        $user = $users->find($id)->current();
        
        $this->logger->notice("Loading items owned by user '{$id}'");
        $itemsTable = new Item();
        $items = $itemsTable->fetchAllByUser($user);
        
        $this->logger->info('Passing the title and user to the view');
        $this->view->assign(array(
            'title'    => $user->name,
            'user'     => $user,
            'items'    => $items
        ));
        
        $this->logger->info('Render view');
        $this->render();
        
        $this->logger->exiting();
    }
    
    public function editAction()
    {
        $this->logger->entering();
        
        if ($user = $this->_getParam('user')) {
            $this->logger->info("Got the user '{$user}' from params");
        } else {
            $this->logger->debug('Getting the id');
            $id = $this->_getParam('id');
        
            $this->logger->info("Loading the user by id '{$id}'");
            $users = new User;
            $user = $users->find($id)->current();
            
            $this->logger->info('Ensure logged in as user');
            if (!isset($this->session->user_id) ||
                $this->session->user_id != $user->id)
            {    
                $this->flash->notice = "Invalid Action";
                $this->_redirect('/');
            }
            
            unset($this->flash->notice);
        }
        
        $this->logger->info('Populating the view');
        $this->view->assign(array(
            'title' => "Editing {$user->name}'s details",
            'states' => $this->states,
            'user'  => $user
        ));
        
        $this->logger->info('Render view');
        $this->render();
        
        $this->logger->exiting();
    }
    
    public function updateAction()
    {
        $this->logger->entering();
        
        $this->logger->debug('Getting the user from form data');
        $user = $this->_getParam('user');

		$this->logger->info("Loading the user row '{$user['id']}' from db");
		$users = new User;
		$userRow = $users->find($user['id'])->current();
	
		$this->logger->info('Ensure logged in as user');
		if ($this->session->user_id != $userRow->id)
		{    
			$this->flash->notice = "Invalid Action";
			$this->redirect('/');
		}
	
		$this->logger->notice("Updating the user row from user");
		$userRow->setFromArray($user);
	
		if ($userRow->save()) {
			$this->_redirect("user/show/{$user['id']}");
		} 
            
        $this->logger->exiting();
    }
    
    public function destroyAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Getting user from params');
        $user = $this->_getParam('user');
        $users = new User();
        $user = $users->find($user['id'])->current();
        
        $this->logger->info("Ensuring logged in as user '{$user->id}'");
        if ($this->session->user_id != $user->id)
        {
            $this->logger->warn('Not allowed to delete this user');
            $this->flash->notice = "Invalid Action";
            $this->_redirect('/');
        }
        
        $this->logger->notice('Deleting items of user');
        $users->deleteItems($user);
        
        $this->logger->notice("Deleting user by id '{$user->id}'");
        $where = $this->db->quoteInto('id = ?', $user->id);
        $users->delete($where);
        
        $this->logger->info('Logging out');
        unset($this->session->user_id);
        
        $this->logger->info('Redirecting to index');
        $this->flash->notice = "User account deleted";
        $this->_redirect('/');
        
        $this->logger->exiting();
    }
}
