<?php
require_once 'Zend/Db/Table.php';
require_once 'Tag.php';
require_once 'Swaplady/Log.php';

class Item extends Zend_Db_Table
{
	protected $_name = 'items';
	protected $_dependentTables = array('LineItem');
    protected $_referenceMap    = array(
        'Owner' => array(
            'columns'           => array('owner_id'),
            'refTableClass'     => 'User',
            'refColumns'        => 'id'
        ),
		'Image' => array(
            'columns'           => array('image_id'),
            'refTableClass'     => 'Image',
            'refColumns'        => 'id'
        )
    );
  
  public function findTags($id)
  {
        Zend_Registry::get('logger')->entering();
        
		$tags = new Tag();
		$subquery = $this->_db->select();
		$subquery->from('tags_items', 'tag_id');
		$subquery->where('item_id = ?', $id);
		return $tags->fetchAll('id IN (' . $subquery . ')');
		
        Zend_Registry::get('logger')->exiting();
  }
  
  // Associate all tags with the item, creating tags when necessary
  public function insertTags($item, $tagString)
  {
        Zend_Registry::get('logger')->entering();
        
        $tags = Tag::parseTags($tagString);
        
		foreach ($tags as $tagName) {
			Zend_Registry::get('logger')->debug("Got tagname: '{$tagName}'");
			// Find or create new tag
			$tags = new Tag();
			$where = $this->_db->quoteInto('name = ?', $tagName);
			$tag = $tags->fetchRow($where);
			Zend_Registry::get('logger')->debug("Got tag: '{$tag->id}', '{$tag->name}'");
			if ($tag->id) {
				$tagId = $tag->id;
			} else {
				$row = array('name' => $tagName);
				$tagId = $tags->insert($row);
			}
			
			// Create association
			$this->_db->insert('tags_items',
								array('item_id' => $item->id,
									  'tag_id'  => $tagId));
		}
      
		Zend_Registry::get('logger')->exiting();
  }
  
  // Delete all exiting tags from item
  public function deleteTags($id)
  {
		Zend_Registry::get('logger')->entering();
		
		$where = $this->_db->quoteInto('item_id = ?', $id);
		$this->_db->delete('tags_items', $where);
      
        Zend_Registry::get('logger')->exiting();
  }
  
  public function updateTags($id, $tags)
  {
      Zend_Registry::get('logger')->entering();

      Zend_Registry::get('logger')->debug('Delete exiting tags on item');
      $this->deleteTags($id);
      Zend_Registry::get('logger')->debug('Insert new tags on item');
      $this->insertTags($id, $tags);

      Zend_Registry::get('logger')->exiting();
  }
  
  public static function fetchAllUnsold()
  {
  		$items = new self();
		return $items->fetchAll('sold <=> NULL');
  }

  public function fetchAllByUser($user)
  {
      Zend_Registry::get('logger')->entering();

      Zend_Registry::get('logger')->debug('Select all items by user id');
      $where = $this->_db->quoteInto('owner_id = ?', $user->id);
      return $this->fetchAll($where);

      Zend_Registry::get('logger')->exiting();
  }
  
    public static function shippingCharge($weight)
    {
        Zend_Registry::get('logger')->entering();
        if ($weight <= 2) {
            $charge = 5;
        } elseif ($weight <= 5) {
            $charge = 8;
        } elseif ($weight <= 10) {
            $charge = 10;
        } elseif ($weight <= 15) {
            $charge = 15;
        } elseif ($weight <= 20) {
            $charge = 18;
        } elseif ($weight <= 25) {
            $charge = 20;
        } elseif ($weight <= 30) {
            $charge = 25;
        } else {
            $charge = 30;
        }
        
        Zend_Registry::get('logger')->exiting();
        return $charge;
    }
    
    public static function calculateCharges($item, $buyer)
    {
        Zend_Registry::get('logger')->entering();
        
        Zend_Registry::get('logger')->debug('Static charges');
        $charges['price'] = $item->points;
        $charges['shipping'] = self::shippingCharge($item->weight);
        $charges['swapFee'] = 1;
        
        Zend_Registry::get('logger')->debug('Split charges into swapbucks and paypal');
        if ($buyer->balance >= $charges['price'] + $charges['shipping']) {
            Zend_Registry::get('logger')->debug('Buyer has enough ');
            $charges['swapbucks'] = $charges['price'] + $charges['shipping'];
            $charges['paypal'] = $charges['swapFee'];
        } else {
            $charges['swapbucks'] = $buyer->balance;
            $charges['paypal'] = $charges['price']
                               + $charges['shipping']
                               + $charges['swapFee']
                               - $buyer->balance;
        }
        
        $charges['total'] = $charges['paypal']
                          + $charges['swapbucks'];
        
        Zend_Registry::get('logger')->exiting();
        return $charges;
    }
}
