<?php
require_once 'DbModel.php';
require_once 'Zend/Db/Table.php';
require_once 'Item.php';
require_once 'Swaplady/Log.php';

class Message extends DbModel
{
    protected $_name = 'messages';
    protected $_referenceMap    = array(
        'Conversation' => array(
            'columns'           => array('conversation_id'),
            'refTableClass'     => 'Conversation',
            'refColumns'        => 'id'
        ),
        
        'Author' => array(
            'columns'           => array('author_id'),
            'refTableClass'     => 'User',
            'refColumns'        => 'id'
        )
    );

}