<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_LoggerTest extends PhpStats_UnitTestCase
{   
    function testLog()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array() );
        $event = $this->findEvent();
        $this->assertNotEquals( 0, $event->getId(), 'logging an event assigns it an event ID' );
    }
    
    function testLogHost()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', '127.0.0.1', array() );
        $event = $this->findEvent();
        $this->assertEquals( '127.0.0.1', $event->getHost(), 'logging an event records its host (IP Address)' );
    }
    
    function testLogAttribute()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(
            'attribute' => 'value'
        ));
        
        $event = $this->findEvent();
        $attributes = $event->getAttributes();
        $this->assertTrue( is_array( $attributes ));
        $this->assertEquals( array( 'attribute' => 'value' ), $attributes, 'passing an associative array of a single paramater persists the paramater' );
    }
    
    function testLogAttributeMultiple()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(
            'attribute' => 'value',
            'attributes2' => 'multiple2'
        ));
        
        $event = $this->findEvent();
        $attributes = $event->getAttributes();
        $this->assertTrue( is_array( $attributes ));
        $expected = array(
            'attribute' => 'value',
            'attributes2' => 'multiple2'
        );
        $this->assertEquals( $expected, $attributes, 'passing an associative array of a multiple paramaters persists the paramaters' );
    }
    
    function testLogDatetimeNow()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array() );
        $event = $this->findEvent();
        $this->assertEquals( time(), $event->getDateTime(), 'logging an event automatically records the date-time as "now"', 10 );
    }  
    
    function testLogDatetime()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(), 12345 );
        $event = $this->findEvent();
        $this->assertEquals( 12345, $event->getDateTime(), 'overriding the date time arguments with a timestamp persists that as the date-time instead of now' );
    }  
    
}