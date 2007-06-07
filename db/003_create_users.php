<?php
/*
 * Create the users table
 *  username: string
 *  name: string
 *  address: string
 *  city: string
 *  state: string
 *  phone: string
 *  email: string
 *  paypal: string
 *  password: string
 */
require_once 'Zend/DB.php'

$db = Zend::registry('db')

$sql = 'CREATE TABLE users
        (
          id          int(255) AUTO_INCREMENT PRIMARY KEY,
          username    varchar(255),
          name        varchar(255),
          address     varchar(255),
          city        varchar(255),
          state       varchar(255),
          phone       varchar(255),
          email       varchar(255),
          password    varchar(255),
          paypal      varchar(255)
        );'

$db->query($sql);
