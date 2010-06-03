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
     abstract function getCompactedCount( $eventType, $attributes = array(), $unique = false );
}

class My_Solid extends My_Abstract
{
    function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        
    }
}