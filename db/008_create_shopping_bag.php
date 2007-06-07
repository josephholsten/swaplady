<?php
$sql = 'CREATE TABLE line_items
        (
            id int(255) AUTO_INCREMENT PRIMARY KEY,
            user_id int(255),
            product_id int(255)
         );'