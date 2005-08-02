<?php
class LoggerTest extends PhpStats_UnitTestCase
{
    function setUp()
    {
        $this->db()->query( 'truncate table `event`' );
        $this->db()->query( 'truncate table `event_attributes`' );
    }
    
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
    
    protected function getLogger()
    {
        $logger = new PhpStats_Logger;
        return $logger;
    }
    
    protected function findEvent()
    {
        $row = $this->findEvents()
            ->fetchObject();
        return new PhpStats_Event( $row );
    }
    
    protected function findEvents()
    {
        $select = $this->db()->select()
            ->from( 'event');
        return $select->query( Zend_Db::FETCH_OBJ );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
    
    
}