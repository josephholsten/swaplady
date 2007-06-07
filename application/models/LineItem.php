<?php
require_once 'Zend/Db/Table.php';
require_once 'Zend/Db/Table/Rowset.php';
require_once 'Swaplady/Log.php';
require_once 'User.php';
require_once 'Item.php';

class LineItem extends Zend_Db_Table_Abstract
{
    protected $_name = 'line_items';
    
    protected $_referenceMap    = array(
        'User' => array(
            'columns'           => 'user_id',
            'refTableClass'     => 'User',
            'refColumns'        => 'id'
        ),
        'Item' => array(
            'columns'           => 'item_id',
            'refTableClass'     => 'Item',
            'refColumns'        => 'id'
        )
    );
    
    static public function isValid(array &$lineitem)
    {
        Zend_Registry::get('logger')->entering();
        
        Zend_Registry::get('logger')->debug('Ensure buyer isn\'t the seller');
        $items = new Item();
        $item = $items->find($lineitem['item_id'])->current();
        if (isset($item->owner_id) && $lineitem['user_id'] == $item->owner_id) {
            $lineitem['errors']['buyer'] = "owns this item";
        }
        
        if (self::exists($lineitem['user_id'], $lineitem['item_id'])) {
            Zend_Registry::get('logger')->debug('Lineitem already exists');
            $lineitem['errors']['item'] = 'is already in your shopping bag';
        } else {            
            Zend_Registry::get('logger')->debug('No such line item found');
        }
        
        if (isset($lineitem['errors'])) {
            $ret = FALSE;
        } else {
            $ret = TRUE;
        }
        return $ret;
        
        Zend_Registry::get('logger')->exiting();
    }

    static public function findItems($id)
    {
        $items = new Item();
        $subquery = $items->_db->select();
        $subquery->from('line_items', 'item_id');
        $subquery->where('user_id = ?', $id);
        return $items->fetchAll('id IN (' . $subquery . ')');
    }
    
    public static function exists($user_id, $item_id)
    {
        $ret = null;
        
        Zend_Registry::get('logger')->debug('Build database query');
        $lineitems = new LineItem();
        $db = $lineitems->_db;
        $select = $db->select();
        $select->from('line_items', '*');
        $select->where('user_id = ?', $user_id);
        $select->where('item_id = ?', $item_id);
        
        Zend_Registry::get('logger')->debug('Find matching line item');
        $result = $db->fetchAll($select);
        
        if ($result != null) {
            $ret = true;
        } else {
            $ret = false;
        }
        
        return $ret;
    }
    
	public static function findByUserAndItem($userId, $itemId)
    {
        $lineItems = new LineItem();
        $db = $lineItems->_db;
        $select = $db->select();
        $select->from('line_items', '*');
        $select->where('user_id = ?', $userId);
        $select->where('item_id = ?', $itemId);
		return $db->fetchAll($select);

/* 		$where = $lineItems->_db->quoteInto('user_id = ? AND', $userId); */
/* 		$where += $lineItems->_db->quoteInto('item_id = ?', $itemId); */
/*         return $lineItems->fetchRow($where); */
    }
}