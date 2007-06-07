<?php
require_once 'DbModel.php';
require_once 'Zend/Db/Table.php';
require_once 'Swaplady/Log.php';

require_once 'Item.php';
require_once 'User.php';
require_once 'Message.php';

class Conversation extends DbModel
{
    protected $_name = 'conversations';
	protected $_dependentTables = array('Message');
    protected $_referenceMap    = array(
        'User' => array(
            'columns'           => array('user_id'),
            'refTableClass'     => 'User',
            'refColumns'        => 'id'
        ),
		'Item' => array(
            'columns'           => array('item_id'),
            'refTableClass'     => 'Item',
            'refColumns'        => 'id'
        )
    );
	
	public function findAllByItem($item)
	{
		$where = $this->_db->quoteInto('item_id = ?', $item->id);
		return $this->fetchAll($where);
	}
	
	public function findByUserAndItem($user, $item)
	{	
		$where = $this->_db->quoteInto('user_id = ? AND ', $user->id);
		$where .= $this->_db->quoteInto('item_id = ?', $item->id);
        return $this->fetchRow($where);
	}	
}