<?php
require_once 'Zend/Db/Table.php';
require_once 'PaypalEntry.php';
require_once 'SwapbuckEntry.php';
require_once 'ItemEntry.php';
require_once 'User.php';
require_once 'Swaplady/Log.php';
require_once 'Item.php';

class Transaction extends Zend_Db_Table {
    protected $_name = 'transactions';
    const TRANSACTION_CHARGE = 1;
    const SIGNUP_BONUS = 10;
    const POST_BONUS = 1;

    public function create()
    {
        $transaction = array('date' => time());
        return parent::insert($transaction);
    }
    
    public function fetchAllByUser($user)
    {
        Zend_Registry::get('logger')->entering();

        $select = $this->_db->select();
        $select->from('transactions', '*');
        $select->join('entries', 'transactions.id = entries.transaction_id', '*');
        $select->where('user_id = ?', $user->id);
        $select->order('date');
        
        Zend_Registry::get('logger')->debug($select);
        return $this->_db->fetchAll($select);

        Zend_Registry::get('logger')->exiting();
    }

    public function paypalToSwapbucks($user, $ammount)
    {
        Zend_Registry::get('logger')->entering();
        
        $transactionId = $this->create();
        $users = new User();
        $swaplady = $users->fetchRow('username = "swaplady"');
        
        PaypalEntry::transfer($transactionId, $user, $swaplady, $ammount);
        SwapbuckEntry::transfer($transactionId, $swaplady, $user, $ammount);
        
        Zend_Registry::get('logger')->exiting();
        return $transactionId;
    }
    
    public function swapCart($user)
    {
        Zend_Registry::get('logger')->entering();
        
        Zend_Registry::get('logger')->debug('Instantiate transaction');
        $transactionId = $this->create();
        
        Zend_Registry::get('logger')->debug('Find items to buy');
        $lineItemTable = new LineItem();
        $items = $lineItemTable->findItems($user->id);
        $lineItems = $user->findLineItem();
        
        Zend_Registry::get('logger')->debug('Calculate charges & fees');
        $totalCharges = 0;
        $totalFees = 0;
/*
        foreach ($items as $item)
        {
            $totalCharges += $item->points + Item::shippingCharge($item->weight);
            $totalFees += self::TRANSACTION_CHARGE;
        }
*/
        
        foreach ($lineItems as $lineItem) {
        	$item = $lineItem->findParentItem();
			$totalCharges += $item->points;
        	if (1 == $lineItem->shipping)
        		$totalCharges += Item::shippingCharge($item->weight);
        	$totalFees += self::TRANSACTION_CHARGE;
        }
        
        
        Zend_Registry::get('logger')->debug('Calculate swapbucks to buy');
        $swapbucksToBuy = $totalCharges - $user->balance;
        if ($swapbucksToBuy < 0) {
            $swapbucksToBuy = 0;
        }

        Zend_Registry::get('logger')->debug('Find the swaplady user');
        $users = new User();
        $swaplady = $users->fetchRow('username = "swaplady"');
        
        Zend_Registry::get('logger')->debug('Transfer charges & fees from paypal');
        $totalPaypalTransfer = $totalCharges + $totalFees;
        PaypalEntry::transfer($transactionId, $user, $swaplady, $totalPaypalTransfer);
        
        Zend_Registry::get('logger')->debug('Transfer bought swapbucks');
        SwapbuckEntry::transfer($transactionId, $swaplady, $user, $swapbucksToBuy);
        
        Zend_Registry::get('logger')->debug('Transfer items');
        foreach ($items as $item) {
            $this->swapItem($transactionId, $user, $item);
        }
        
        Zend_Registry::get('logger')->exiting();
    }
    
    public function swapItem($transactionId, $purchaser, $item)
    {
        Zend_Registry::get('logger')->entering();
        
        $users = new User();
        $owner = $users->find($item->owner_id)->current();
        
        Zend_Registry::get('logger')->debug('Transfer swapbuck value from purchaser to owner');
        SwapbuckEntry::transfer($transactionId, $purchaser, $owner, $item->points + Item::shippingCharge($item->weight));
        
        Zend_Registry::get('logger')->debug('transfer item from owner to purchaser');
        ItemEntry::transfer($transactionId, $owner, $purchaser, $item);
        
        Zend_Registry::get('logger')->exiting();
        return $transactionId;
    }
    
    public function postItem($user)
    {
        Zend_Registry::get('logger')->entering();
        
        $transactionId = $this->create(array());
        $users = new User();
        $swaplady = $users->fetchRow('username = swaplady');
        
        Zend_Registry::get('logger')->debug('Transfer post bonus from swaplady to user');
        SwapbuckEntry::transfer($transactionId, $swaplady, $user, self::POST_BONUS);
        
        Zend_Registry::get('logger')->exiting();
        return $transactionId;
    }
    
    public function signupUser($user)
    {
        Zend_Registry::get('logger')->entering();
        
        $transactionId = $this->create();
        $users = new User();
        $swaplady = $users->fetchRow('username = "swaplady"');
        
        Zend_Registry::get('logger')->debug('Transfer signup bonus from swaplady to user');
        SwapbuckEntry::transfer($transactionId, $swaplady, $user, self::SIGNUP_BONUS);
        
        Zend_Registry::get('logger')->exiting();
        return $transactionId;
    }
}
