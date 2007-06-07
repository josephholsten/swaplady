<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Mail.php';
require_once 'Zend/View.php';
require_once 'Zend/Date.php';

require_once 'Swaplady/Log.php';
require_once 'ApplicationController.php';

require_once 'User.php';
require_once 'Item.php';
require_once 'Message.php';
require_once 'Conversation.php';

class ConversationsController extends ApplicationController
{
	public function showAction()
	{
		$this->logger->entering();
		
		$this->logger->info('Loading conversation');
		$conversations = new Conversation();
		$conversation = $conversations->find($this->_getParam('id'))->current();
		
		$this->logger->info('Loading Item');
		$item = $conversation->findParentItem();
		
		$this->logger->info('Ensure authorized to view');
		if ($this->session->user_id != $conversation->user_id &&
			$this->session->user_id != $item->owner_id)
		{
			$this->flash->notice = "Invalid Action";
			$this->_redirect('/');
		}
		
		$this->logger->info('Loading Messages');
		$messageRows = $conversation->findMessage();
		
		foreach($messageRows as $messageRow) {
			$message = $messageRow->toArray();
			$message['user'] = $messageRow->findParentUser()->toArray();
			$messages[] = $message;
		}

		$this->logger->info('Loading View');
		$this->view->assign(array(
			'conversation' => $conversation,
			'messages'     => $messages,
			'item'         => $item
		));
		
		$this->logger->info('Rendering view');
		$this->render();
		
		$this->logger->exiting();
	}
}