<?php
require_once 'Zend/Db.php';
require_once 'ApplicationController.php';
require_once 'Zend/View.php';
require_once 'Image.php';

/*
 * Image Controller
 * Handle the showing, creation, and deletion of images
 */
class ImageController extends ApplicationController
{
    public function newAction()
    {
        $this->logger->entering();

        $this->logger->info('Loading view parameters');
        $this->view->assign(array(
            'title' => 'New image'
        ));
        
        $this->logger->info('Rendering the application template');
        $this->render();
        
        $this->logger->exiting();
    }

    public function createAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Reading Image data from temprary storage');
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
                        
        $this->logger->info('Building image from the reqeust');
        $imageParams = array(
            'name'         => $_FILES['image']['name'],
            'content_type' => $_FILES['image']['type'],
            'data'         => $imageData
        );
        
        $this->logger->info('Inserting image');
        $images = new Image();
        $image = $image->fetchNew();
        $image->setFromArray($imageParams);
        $image->save();
        
        $this->logger->info('Redirect to show the image');
        $this->_redirect("image/show/id/{$image->id}");
        
        $this->logger->exiting();
    }

    public function showAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Load the image by id');
        $images = new Image();
        $image = $images->find($this->_getParam('id'))->current();
        
        $this->logger->info('Generating response');
        $this->getResponse()
             ->setHeader('Content-Type', $image->content_type)
             ->setHeader('Content-Disposition',
                         "inline; filename=\"{$image->name}\"")
             ->setBody($image->data);
        
        
        $this->logger->exiting();
    }
    
    public function destroyAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Quoting to protect against injection');
        $where = $this->db->quoteInto('id = ?', $this->_getParam('id'));
        
        $this->logger->info('Deleting the image');
        $images = new Image();
        $images->delete($where);
        
        $this->logger->info('Redirecting to new image action');
        $this->_redirect('image/new');
        
        $this->logger->exiting();
    }
}