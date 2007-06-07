<?php
require_once 'Zend/Db/Table.php';

class Image extends Zend_Db_Table {
	protected $_name = 'images';
  
	protected $_dependentTables = array('Item');
	
	public static function readFromTempStorage(&$name, $size)
	{
		return fread(fopen($name,'r'), $size);
	}
}