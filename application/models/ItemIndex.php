<?php
require_once 'Zend/Search/Lucene.php';
require_once 'Item.php';

class ItemIndex
{
	static public function insert($item, $tags)
	{
		$index = self::open();
		self::_insert($index, $item, $tags);
	}
	
	static private function _insert($index, $item, $tags)
	{
		$doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('title', $item->name));
        $doc->addField(Zend_Search_Lucene_Field::Text('item_id', strval($item->id)));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('image_id', strval($item->image_id)));
        $doc->addField(Zend_Search_Lucene_Field::Text('description', $item->description));
        $doc->addField(Zend_Search_Lucene_Field::Text('tag', $tags));
        
        $index->addDocument($doc);
        $index->commit();

	}
	
	static public function update($item, $tags) 
	{
		$index = self::open();
		self::_update($index, $item, $tags);
	}
	
	static private function _update($index, $item, $tags)
	{
		self::_delete($index, $item);
        self::_insert($index, $item, $tags);
	}
	
	static public function delete($item)
	{
		$index = self::open();
		self::_delete($index, $item);
	}
	
	static private function _delete($index, $item)
	{
        $hits = $index->find($item->name);
        foreach ($hits as $hit) {
            if ($hit->item_id == $item->id) {
                $index->delete($hit->id);
                $index->commit();
            }
        }   
	}    
	
	static public function find($query)
	{
		$index = self::open();
		return self::_find($index, $query);
	}
	
	static private function _find($index, $query)
	{
        return $index->find($query);
	}
	
	static public function rebuild()
	{
        $index = self::create();

	    $itemTable = new Item();
        $items = $itemTable->fetchAll();
        
        foreach ($items as $item) {
            $tags = '';
            foreach ($itemTable->findTags($item->id) as $tag) {
                $tags .= $tag->name . ' ';
            }
            self::_insert($index, $item, $tags);
        }
	}
	
	static public function open()
	{
		return Zend_Search_Lucene::open('db/itemIndex');
	}
	
	static public function create()
	{
	    return Zend_Search_Lucene::create('db/itemIndex');
	}
}