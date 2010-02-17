<?php
class PhpUnitTest extends PHPUnit_Framework_TestCase
{
    function test1()
    {
        $a = new My_Solid();
        $a->getCompactedCount();
    }
}

abstract class My_Abstract
{
     abstract public function getCompactedCount( $eventType, $attributes = array(), $unique = false );
}

class My_Solid extends My_Abstract
{
    public function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        
    }
}