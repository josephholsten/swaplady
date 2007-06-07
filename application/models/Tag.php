<?php
require_once 'Zend/Db/Table.php';
require_once 'Item.php';

class Tag extends Zend_Db_Table
{
    protected $_name = 'tags';
    
    public function findItems($id)
    {
        $items = new Item();
        $subquery = $this->_db->select();
        $subquery->from('tags_items', 'item_id');
        $subquery->where('tag_id = ?', $id);
        return $items->fetchAll('id IN (' . $subquery . ')');
    }
    
    static public function joinTags($tags)
    {
        $tagString = '';
        foreach ($tags as $tag) {
            $tagString = $tagString . $tag->name . ' ';
        }
        return $tagString;
    }
    
    static public function findTopTags()
    {
        $tagTable = new self();
        
        $query = $tagTable->_db->query('
        SELECT (
                SELECT count(*)
                FROM tags_items
                WHERE tags_items.tag_id = tags.id
            ) AS number, 
            tags.*
            FROM tags
            ORDER BY number DESC
            LIMIT 25;');
        
        return $query->fetchAll();
    }
    
    static public function parseTags($string)
    {
        return preg_split('/\W+/', $string, -1, PREG_SPLIT_NO_EMPTY);
    }
    
}