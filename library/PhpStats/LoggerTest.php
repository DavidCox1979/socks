<?php
class LoggerTest extends PhpStats_UnitTestCase
{
    function setUp()
    {
        $this->db()->query( 'truncate table `event`' );
    }
    
    function testLog()
    {
        $logger = $this->getLogger();
        $logger->log( 'click', array() );
        $row = $this->findEvent();
        $this->assertNotEquals( 0, $row->id, 'an event has been logged' );
    }
    
    protected function getLogger()
    {
        $logger = new PhpStats_Logger;
        return $logger;
    }
    
    protected function findEvent()
    {
        return $this->findEvents()
            ->fetchObject();
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