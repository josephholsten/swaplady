<?php
require_once 'Entry.php';

class SwapbuckEntry extends Entry
{
    public function insert(array $params)
    {
        // set as swapbuck entry
        if(empty($params['type'])) {
            $params['type'] = 'swapbuck';
        }
        
        return parent::insert($params);
    }
    
    static public function transfer($transaction_id, $fromUser, $toUser, $ammount)
    {
        Zend_Registry::get('logger')->entering();
        $klass = get_class($toUser);
        Zend_Registry::get('logger')->debug("toUser is a {$klass}");
        
        
        $swapbuckEntry = new SwapbuckEntry();
        
        Zend_Registry::get('logger')->info("increment debitor:{$toUser->id} swapbucks points");
        $toUser->balance += $ammount;
        $toUser->save();


        Zend_Registry::get('logger')->info("record credit to debitor:{$toUser->id} swapbucks account");
        $credit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $toUser->id,
            'ammount'        => $ammount
        );
        $swapbuckEntry->insert($credit);

        Zend_Registry::get('logger')->info("decrement debitor:{$fromUser->id} swapbucks points");
        $fromUser->balance += -1 * $ammount;
        $fromUser->save();

        Zend_Registry::get('logger')->info("record debit to creditor:{$toUser->id} swapbucks account");
        $debit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $fromUser->id,
            'ammount'        => -1 * $ammount
        );
        $swapbuckEntry->insert($debit);
        
        Zend_Registry::get('logger')->exiting();
    }
}