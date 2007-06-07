<?php
require_once 'DbModel.php';
require_once 'Zend/Db/Table.php';
require_once 'Item.php';
require_once 'Swaplady/Log.php';

class User extends DbModel
{
    protected $_name = 'users';
	protected $_dependentTables = array('LineItem, Item');
    
    public static function isValid(array &$user)
    {
        Zend_Registry::get('logger')->entering();
        
        User::ensurePresence('username', $user);
        User::ensurePresence('password', $user);
        User::ensurePresence('email', $user);
        User::ensurePresence('name', $user);
        User::ensurePresence('address', $user);
        User::ensurePresence('city', $user);
        User::ensurePresence('state', $user);
        User::ensurePresence('zip_code', $user);
        User::ensurePresence('country', $user);
        // User::ensurePresence('paypal', $user);
        
        User::ensureMatch('password', 'password_confirm', $user);
        User::ensureMatch('email', 'emailConfirm', $user);
        
        Zend_Registry::get('logger')->debug('Ensure unique username');
        if (self::exists($user['username'])) {
            $user['errors']['username'] = "already exists";
        }
        
        if (isset($user['errors'])) {
            $ret = FALSE;
        } else {
            $ret = TRUE;
        }
        return $ret;
        
        Zend_Registry::get('logger')->exiting();    
    }
    
    public function deleteProducts($user)
    {
        Zend_Registry::get('logger')->entering();
        
        Zend_Registry::get('logger')->debug('Load all items by user');
        $itemTable = new Item();
        $items = $itemTable->fetchAllByUser($user);

        foreach ($items as $item) {
            Zend_Registry::get('logger')->debug('Delete all tags from item');
            $itemTable->deleteTags($item->id);
            Zend_Registry::get('logger')->debug('Delete item');
            $where = $this->_db->quoteInto('id = ?', $item->id);
            $itemTable->delete($where);
        }
        
        Zend_Registry::get('logger')->exiting();
    }
    
    public function deleteItems($user)
    {
        Zend_Registry::get('logger')->entering();
        
        Zend_Registry::get('logger')->debug('Load all items by user');
        $itemTable = new Item();
        $items = $itemTable->fetchAllByUser($user);

        foreach ($items as $item) {
            Zend_Registry::get('logger')->debug('Delete all tags from item');
            $itemTable->deleteTags($item->id);
            Zend_Registry::get('logger')->debug('Delete item');
            $where = $this->_db->quoteInto('id = ?', $item->id);
            $itemTable->delete($where);
        }
        
        Zend_Registry::get('logger')->exiting();
    }
    
    public static function exists($username)
    {
        Zend_Registry::get('logger')->debug('Find matching line item');
        $users = new User();
        $where = $users->_db->quoteInto('username = ?', $username);
        $result = $users->fetchAll($where);
        
        return $result->exists();
    }

}
