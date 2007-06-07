<?php
require_once 'Zend/Db.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Search/Lucene.php';
require_once 'Zend/View.php';
require_once 'ApplicationController.php';
require_once 'Swaplady/Log.php';
require_once 'Image.php';
require_once 'Item.php';
require_once 'Tag.php';
require_once 'Message.php';
require_once 'ItemIndex.php';

/*
 * Items Controller
 * Handle the showing, creating, updating, and deleting of Items
 */
class ItemsController extends ApplicationController
{
    public function preDispatch()
    {
        $this->filterActions('ensurePost',
            array('create', 'update', 'destroy'));
        $this->filterActions('ensureLoggedIn',
            array('new', 'create', 'edit', 'update', 'destroy'));
    }
    
    public function indexAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Loading view parameters');
        $this->view->assign(array(
            'title'    => 'Item Index',
            'items'    => Item::fetchAllUnsold()
        ));
        
        $this->logger->info('Rendering the application template');
        $this->render();
        
        $this->logger->exiting();
    }

    public function newAction()
    {
        $this->logger->entering();

        $this->logger->info('Loading view parameters');
        $this->view->assign(array(
            'title'   => 'Post an Item',
            'toptags' => Tag::findTopTags()
        ));
        
        $this->logger->info('Rendering the application template');
		$this->render();
        
        $this->logger->exiting();
    }

    public function createAction()
    {
        $this->logger->entering();

        $this->logger->info('Reading Image data from temporary storage');
        $image_data = file_get_contents($_FILES['image']['tmp_name']);

        $this->logger->info('Building row data from image');
        $imageRow = array(
            'name' => $_FILES['image']['name'],
            'content_type' => $_FILES['image']['type'],
            'data' => $image_data
        );
        
        $this->logger->info('Inserting Image into database');
        $images = new Image();
        $imageId = $images->insert($imageRow);
        
        
        $this->logger->info('Creating a new item');
        $items = new Item();
        $item = $items->fetchNew();
        $item->setFromArray($this->_getParam('item'));
        $item->owner_id = $this->session->user_id;
        $item->image_id = $imageId;
        $item->save();
        
        $this->logger->info('Inserting item tags');
        $items->insertTags($item, $this->_getParam('tags'));
        
        $this->logger->info('Building search index document');
        ItemIndex::insert($item, $this->_getParam('tags'));
        
        $this->logger->info('Redirecting to show the item');
        $this->_redirect("items/show/{$item->id}");
        
        $this->logger->exiting();
    }

    public function showAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Loading the item by id');
        $items = new Item();
        $item = $items->find($this->_getParam('id'))->current();
        
        if ($item == null) {
        	$this->logger->warn('Tried to show an non-existant item');
        	$this->flash->notice = 'Invalid Action';
        	$this->_redirect('/');
        }
        
        $this->logger->info('Find all the tags for the item');
        $tags = $items->findTags($item->id);
        
        $this->logger->info('Find owner');
        $users = new User();
		$owner = $item->findParentUser();
        $user = $users->find($this->session->user_id)->current();
        
        $this->logger->info('Calculating price total');
        $shipping = Item::shippingCharge($item->weight);
        $total = $item->points + $shipping;
        
        $this->logger->info('Loading existing conversations');
        $conversation = null;
        $conversations = null;
        if (isset($user)) {
			$conversationTable = new Conversation();
			if ($owner->id == $user->id) {
				$conversationRows = $conversationTable->findAllByItem($item);
				foreach ($conversationRows as $conversationRow) {
					$convo = $conversationRow->toArray();
					$convo['user'] = $conversationRow->findParentUser()->toArray();
					$conversations[] = $convo;
				}
			} else {
				$conversation = $conversationTable->findByUserAndItem($user, $item);
			}
		}
        
        $this->logger->info('Loading the view parameters');
        $this->view->assign(array(
            'title'         => "Item: {$item->name}",
            'item'          => $item,
            'owner'         => $owner,
            'tags'          => $tags,
            'shipping'      => $shipping,
            'total'         => $total,
            'conversations' => $conversations,
            'conversation'  => $conversation
        ));
        
        $this->logger->info('Rendering the application template');
        $this->render();
        
        $this->logger->exiting();
    }

    public function editAction()
    {
        $this->logger->entering();

        $this->logger->info('Loading most popular tags');
        $toptags = Tag::findTopTags();
        
        $this->logger->info('Loading the item by id');
        $items = new Item();
        $item = $items->find($this->_getParam('id'))->current();
        
        $this->logger->info('Ensure user is item owner');
        if ($item->owner_id != $this->session->user_id) {
            $this->logger->warn('User is not item owner');
            $this->flash->notice = "Invalid Action";
            $this->_redirect('/');
        }
        
        $this->logger->info('Loading the view parameters');
        $this->view->assign(array(
            'title'    => "Editing {$item->name}",
/*             'view'     => 'itemsEdit.phtml', */
            'item'     => $item,
            'tags'     => Tag::joinTags($items->findTags($item->id)),
            'toptags'  => $toptags
        ));
        
        $this->logger->info('Rendering the application template');
/*         echo $this->view->render('applicationTemplate.phtml'); */
        $this->render();
        
        $this->logger->exiting();
    }

    public function updateAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Loading the item by id');
        $items = new Item();
        $item = $items->find($this->_getParam('id'))->current();
        
        $this->logger->info('Setting item from params');
        $item->setFromArray($this->_getParam('item'));
        
        if (isset($_FILES) &&
            isset($_FILES['image']) &&
            $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $this->logger->notice('Item image has changed');
            
            $this->logger->info("Reading image data from temporary storage '{$_FILES['image']['tmp_name']}'");
            $image_data = file_get_contents($_FILES['image']['tmp_name']);

            $this->logger->info('Building row data from image');
            $imageRow = array(
                'name' => $_FILES['image']['name'],
                'content_type' => $_FILES['image']['type'],
                'data' => $image_data
            );

            $this->logger->info('Inserting Image');
            $images = new Image();
            $images->insert($imageRow);
            
            $this->logger->info('Getting the id of the image');
            $item->image_id = $this->db->lastInsertId();
        }
        
        switch ($_FILES['image']['error']) {
        case UPLOAD_ERR_OK:
            $this->logger->info('Image uploaded without complication');
            break;
        case UPLOAD_ERR_INI_SIZE:
            $this->logger->warn('Image too large');
            $this->flash->notice = "Image too large";
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $this->logger->warn('Image too large');
            $this->flash->notice = "Image too large";
            break;
        case UPLOAD_ERR_PARTIAL:
            $this->logger->warn('Image failed to upload');
            $this->flash->notice = "Image failed to upload, could you try again please?";
            break;
        case UPLOAD_ERR_NO_FILE:
            $this->logger->info('No image uploaded');
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $this->logger->err('File upload directory is missing');
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $this->logger->err('File upload directory is not writable');
            break;
        case UPLOAD_ERR_EXTENSION:
            $this->logger->warn('Unacceptable file extension on uploaded file');
            $this->flash->notice = "Invalid file format. Upload an image please.";
            break;
        default:
            $this->logger->crit("Unknown image upload error '{$_FILES['image']['error']}'");
        }
        
        $this->logger->info('Saving item');
        $item->save();
        
        $this->logger->info('Inserting item tags');
        $tags = Tag::parseTags($this->_getParam('tags'));
        $items->updateTags($item->id, $tags);
        
        $this->logger->info('Adding items to search index');
		ItemIndex::update($item, $this->_getParam('tags'));

        $this->logger->info('Redirecting to show the item');
        $this->_redirect("items/show/{$item->id}");
        
        $this->logger->exiting();
    }
    
    public function destroyAction()
    {
        $this->logger->entering();
        
        $this->logger->info('Loading item from params');
        $items = new Item();
        $item = $this->_getParam('item');
        
        $this->logger->info("Loading item by id");
        $id = $item['id'];
        $item = $items->find($id)->current();
        
        $this->logger->info('Ensure owned by user');
        if ($this->session->user_id != $item->owner_id) {
            $this->logger->warn('User is not item owner');
            $this->flash->notice = "Invalid Action";
            $this->_redirect('/');
        }
        
        $this->logger->info("Delete item by id {$id}");
        $where = $this->db->quoteInto('id = ?', $id);
        $rows_affected = $items->delete($where);
        
        $this->logger->info('Delete item tag associations');
        $items->deleteTags($id);
        
        $this->logger->info("Removing old item from search index");
        ItemIndex::delete($item);
        
        $this->logger->info('Redirect to item index');
        $this->_redirect('items');
        
        $this->logger->exiting();
    }
}