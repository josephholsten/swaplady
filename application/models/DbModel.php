<?php
require_once 'Zend/Db/Table.php';
require_once 'Swaplady/Log.php';

class DbModel extends Zend_Db_Table
{      
    protected static function ensurePresence($field, &$row)
    {
        Zend_Registry::get('logger')->debug("Ensure presence of {$field}");
        if (empty($row[$field])) {
            $row['errors'][$field] = "isn't present";
        }
    }
    
    protected static function ensureMatch($field, $confirmation, &$row)
    {
        Zend_Registry::get('logger')->debug("Ensure {$field} matches {$confirmation}");
        if ($row[$field] != $row[$confirmation]) {
            $row['errors'][$field] = "doesn't match confirmation";
        }
    }
    
        
    /**
     * Filter an array to contain only keys which are columns
     */
    public function filterColumns($data)
    {
    	if (empty($data))
    		$data = array();
        return array_intersect_key($data, array_flip($this->_cols));
    }
}
