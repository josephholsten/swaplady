<?php
require_once 'Zend/Db/Table.php';

class Entry extends Zend_Db_Table
{
    protected $_name = 'entries';
}