<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/View.php';
require_once 'Swaplady/Log.php';
require_once 'ApplicationController.php';
require_once 'Tag.php';

class TagController extends ApplicationController
{
    public function indexAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Load all tags');
        $tagTable = new Tag();
        $tagSet = $tagTable->fetchAll();
        
        $this->logger->info('Load item count for each tag');
        $tags = array();
        foreach ($tagSet as $tag) {
            $tag = $tag->toArray();
            $tag['count'] = $tagTable->findItems($tag['id'])->count();
            $tags[] = $tag;
        }
        
        $this->logger->debug("Got '" . count($tags) . "' tags");
        
        $this->logger->info('Load view parameters');
        $this->view->assign(array(
            'title' => 'Keywords',
            'tags'  => $tags
        ));
        
        $this->logger->info('Render view');
		$this->render(); 
        
        $this->logger->exiting();
    }
    
    public function showAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Finding tag from id param');
        $tags = new Tag();
        $where = $this->db->quoteInto('name = ?', $this->_getParam('tag'));
        $tag = $tags->fetchRow($where);
        
        $this->logger->info('Finding items by tag id');
        $items = $tags->findItems($tag->id);
        
        $this->logger->info('Loading view parameters');
        $this->view->assign(array(
            'title'    => 'Keyword: ' . $tag->name,
            'tag'      => $tag,
            'items'    => $items
        ));
        
        $this->logger->info('Rendering view');
		$this->render();
        
        $this->logger->exiting();
    }
}