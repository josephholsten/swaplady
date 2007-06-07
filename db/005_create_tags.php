<?php
/*
 * Create the tags table
 *  name: string
 */ 
require_once 'Zend/DB.php'

$db = Zend::registry('db')

$sql = 'CREATE TABLE tags
        (
            id   int(255) AUTO_INCREMENT PRIMARY KEY,
            name varchar(255)
        ); '
      .'CREATE TABLE tags_products
        (
           tag_id       int(255),
           product_id   int(255)
        );'

$db->query($sql);
