<?php
class LoggerTest extends PhpStats_UnitTestCase
{   
    function testLog()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array() );
        $event = $this->findEvent();
        $this->assertNotEquals( 0, $event->getId(), 'an event has been logged' );
    }
    
    function testLogAttribute()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array(
            'attribute' => 'value'
        ));
        
        $event = $this->findEvent();
        $attributes = $event->getAttributes();
        $this->assertTrue( is_array( $attributes ));
        $this->assertEquals( array( 'attribute' => 'value' ), $attributes, 'saves & loads a single attribute' );
    }
    
    function testLogAttributeMultiple()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array(
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
        $this->assertEquals( $expected, $attributes, 'saves and loads multiple attributes' );
    }
    
    function testLogDatetimeNow()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array() );
        $event = $this->findEvent();
        $this->assertEquals( time(), $event->getDateTime(), 'records the date time as "now"', 10 );
    }  
    
    function testLogDatetime()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array(), 12345 );
        $event = $this->findEvent();
        $this->assertEquals( 12345, $event->getDateTime(), 'records an arbitrary timestamp' );
    }  
    
}