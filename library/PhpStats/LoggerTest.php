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
        $this->assertEquals( array( 'attribute' => 'value' ), $attributes );
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
        $attributes = $this->findEventAttributes( $row->id );
        return new PhpStats_Event( $row, $attributes );
    }
    
    protected function findEventAttributes( $id )
    {
        $select = $this->db()->select()
            ->from('event_attributes')
            ->where('event_id = ?', $id );
        $rows = $select->query( Zend_Db::FETCH_OBJ )->fetchAll();
        $attributes = array();
        foreach( $rows as $row )
        {
            $attributes[ $row->key ] = $row->value;
        }
        return $attributes;
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