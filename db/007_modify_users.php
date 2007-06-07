<?php
// Remove phone number.
// Add Zip Code
// Add Country

$sql = 'ALTER TABLE users
         DROP COLUMN phone,
         ADD COLUMN zip_code int(10),
         ADD COLUMN country varchar(255);'