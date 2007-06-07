<?php
require_once 'PHPUnit/Framework.php';

class ProductTest extends PHPUnit_Framework_TestCase
{
  public function testTruth()
  {
    $this->assertEquals(1, 1);
  }
  
}