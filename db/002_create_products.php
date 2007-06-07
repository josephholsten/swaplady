<?php
/*
 * Create the products table
 *  image_id: integer
 *  name: string
 *  points: integer
 *  description: string
 *  weight: integer
 */ 
require_once 'Zend/DB.php'

$db = Zend::registry('db')

$sql = 'CREATE TABLE products
        (
            id          int(255) AUTO_INCREMENT PRIMARY KEY,
            image_id    int(255),
            name        varchar(255),
            points      int(255),
            description text,
            weight      int(255)
        );'

$db->query($sql);
