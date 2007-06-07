<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Date.php';
require_once 'Zend/Mail.php';
require_once 'ApplicationController.php';
require_once 'Swaplady/Log.php';
require_once 'Message.php';

class MessageController extends ApplicationController
{
    public function preDispatch()
    {
        $this->filterActions('ensurePost',
            array('create'));
        $this->filterActions('ensureLoggedIn',
            array('create'));
    }
    
    public function createAction()
    {
    	$this->logger->entering();
    	
    	$this->logger->info('Creating a new messsage row');
    	$messages = new Message();
    	$message = $messages->fetchNew();
    	
    	$this->logger->info('Setting message from post parameters');
    	$messageParams = $messages->filterColumns($this->_getParam('message'));
    	$message->setFromArray($messageParams);
    	$date = new Zend_Date();
    	$message->created_on = $date->get(Zend_Date::ISO_8601);
    	
    	$this->logger->info('Creating a new conversation if necessary');
    	if (empty($message->conversation_id)) {
    		$conversations = new Conversation();
    		$conversation = $conversations->fetchNew();
    		$conversationParams = $conversations->filterColumns($this->_getParam('conversation'));
    		$conversation->setfromArray($conversationParams);
    		$conversation->save();
    		
    		$message->conversation_id = $conversation->id;
    	}
    	
    	$this->logger->info('Saving the message');
    	$message->save();
    	
    	$item = $conversation->findParentItem();
    	$owner = $item->findParentOwner();
    	$asker = $conversation->findParentUser();

		$mail = new Zend_Mail();
		$mail->setFrom('swaplady@example.com', 'Swaplady');
		$mail->addTo($owner->email, $owner->name);
		$mail->setSubject("[Swaplady] New message about {$item->name}");
		$mail->setBodyText($message->body . "\nYou can add to the conversation at http://swaplady.com/conversations/show/{$conversation->id}");
		$mail->send();
 
		$mail = new Zend_Mail();
		$mail->setFrom('swaplady@example.com', 'Swaplady');
		$mail->addTo($asker->email, $asker->name);
		$mail->setSubject("[Swaplady] New message about {$item->name}");
		$mail->setBodyText($message->body . "\nYou can add to the conversation at http://swaplady.com/conversations/show/{$conversation->id}");
		$mail->send();   	
    	
    	$this->logger->info('Redirecting to show the item of the message');
    	$this->_redirect('/conversations/show/' . $message->conversation_id);
    }
}