<?php
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Mail.php';
require_once 'Zend/View.php';
require_once 'ApplicationController.php';
require_once 'Swaplady/Log.php';
require_once 'User.php';

class PasswordController extends ApplicationController
{
    public function forgotAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Rendering template');
		$this->render();
        
        $this->logger->exiting();
    }
    
    public function sendAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Get email from params');
        $email = $this->_getParam('email');
        
        $this->logger->info('Getting password for email');
        $users = new User();
        $where = $this->db->quoteInto('email = ?', $email);
        $user = $users->fetchRow($where);
        
        if ($user->id != null) {
            $this->logger->debug("Got user #{$user->id}");
            $this->logger->info('Sending password reminder');
            $mail = new Zend_Mail();
            $mail->setFrom('somebody@example.com', 'Some Sender');
            $mail->addTo($user->email, $user->name);
            $mail->setSubject("Your Swaplady Password");
            $mail->setBodyText("Hi {$user->name},\nHere's your swaplady password:\n{$user->password}\nPlease keep it safe and sound.");
            $mail->send();

            $this->flash->notice = "Your password has been emailed to {$user->email}";
            $this->_redirect('/session/new');
        } else {
            $this->logger->warn('Unknown email');
            $this->flash->notice = "Your email wasn't recognized, did you spell it right?";
            $this->_redirect('/password/forgot');
        }
        
        $this->logger->exiting();
    }
}