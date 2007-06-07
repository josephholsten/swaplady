<?php
require_once 'Entry.php';

class PaypalEntry extends Entry
{
    public function insert(array $params)
    {
        // set to paypal entry
        if(empty($params['type'])) {
            $params['type'] = 'paypal';
        }
        
        return parent::insert($params);
    }
    
    static public function transfer($transaction_id, $fromUser, $toUser, $ammount)
    {
        Zend_Registry::get('logger')->entering();
        
        $paypalEntry = new PaypalEntry();
        
        // record to debitor's paypal account
        $credit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $toUser->id,
            'ammount'        => -1 * $ammount
        );
        $paypalEntry->insert($credit);

        // record to creditor's paypal account
        $debit = array(
            'transaction_id' => $transaction_id,
            'user_id'        => $fromUser->id,
            'ammount'        => $ammount
        );
        $paypalEntry->insert($debit);
        
        Zend_Registry::get('logger')->exiting();
    }
}