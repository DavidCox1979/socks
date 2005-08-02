<?php
class PhpStats_UnitTestCase extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->db()->query( 'truncate table `event`' );
        $this->db()->query( 'truncate table `event_attributes`' );
        $this->db()->query( 'truncate table `hour_event`' );
        $this->db()->query( 'truncate table `hour_event_attributes`' );
        $this->db()->query( 'truncate table `day_event`' );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
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
}