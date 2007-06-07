<?php
/*
 * Create the images table
 *  content-type: string
 *  data: binary
 */ 
require_once 'Zend/DB.php'

$db = Zend::registry('db')

$sql = 'CREATE TABLE images
        (
            id   int(255) AUTO_INCREMENT PRIMARY KEY,
            name varchar(255),
            content_type varchar(255),
            data         blob
        );'

$db->query($sql);
