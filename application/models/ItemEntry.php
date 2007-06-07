<?php
require_once 'Entry.php';

class ItemEntry extends Entry
{
    public function insert(array $data)
    {
        // set to item entry
        if(empty($data['type'])) {
            $data['type'] = 'item';
        }
        
        return parent::insert($data);
    }
    
    static public function transfer($transaction_id, $fromUser, $toUser, $item)
    {
        Zend_Registry::get('logger')->entering();
        
        $itemEntry = new ItemEntry();
        
        // mark item as sold
        $item->sold = 1;
        $item->save();
        
        // record to debitor's paypal account
        $credit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $fromUser->id,
            'ammount'        => -1,
            'item_id'        => $item->id
        );
        $itemEntry->insert($credit);

        // record to creditor's paypal account
        $debit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $toUser->id,
            'ammount'        => 1,
            'item_id'        => $item->id
        );
        $itemEntry->insert($debit);
        
        Zend_Registry::get('logger')->exiting();
    }
}