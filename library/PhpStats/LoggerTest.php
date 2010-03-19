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
        $this->assertEquals( date('j'), $event->getDay(), 'logging an event automatically records the date-time as "now"', 10 );
    }  
    
    function testLogsHour()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(), mktime(3,null,null,2,1,2002) );
        $event = $this->findEvent();
        $this->assertEquals( 3, $event->getHour(), 'event logs the hour' );
    }
    
    function testLogsDay()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(), mktime(null,null,null,2,1,2002) );
        $event = $this->findEvent();
        $this->assertEquals( 1, $event->getDay(), 'event logs the day' );
    }    
    
    function testLogsMonth()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(), mktime(null,null,null,2,1,2002) );
        $event = $this->findEvent();
        $this->assertEquals( 2, $event->getMonth(), 'event logs the month' );
    }    
    
    function testLogsYear()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', null, array(), mktime(null,null,null,2,1,2002) );
        $event = $this->findEvent();
        $this->assertEquals( 2002, $event->getYear(), 'event logs the year' );
    }  
    
}