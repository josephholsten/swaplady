<?php
/**
 * Create the transactions table
 *  date
 */
$sql = 'CREATE TABLE transactions
       (
           id int(255) AUTO_INCREMENT PRIMARY KEY,
           date date
       ); '


/**
 * Create entries table
 *  transaction_id
 *  type
 *  user_id
 *  ammount
 *  item_id
 */
$sql = 'CREATE TABLE entries
       (
           id             int(255) AUTO_INCREMENT PRIMARY KEY,
           transaction_id int(255),
           type           varchar(255),
           user_id        int(255),
           ammount        int(255),
           item_id        int(255)
       ); '
 
/**
 * Add balance column to users table
 */
 $sql = 'ALTER TABLE users
         ADD COLUMN balance int(255);'
/**
 * Add sold column to products table
 */
$sql = 'ALTER TABLE products
        ADD COLUMN sold tinyint;'