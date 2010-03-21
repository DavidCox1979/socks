<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_UnitTestCase extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->db()->beginTransaction();
        $this->db()->query( 'truncate table `socks_event`' );
        $this->db()->query( 'truncate table `socks_event_attributes`' );
        $this->db()->query( 'truncate table `socks_hour_event`' );
        $this->db()->query( 'truncate table `socks_hour_event_attributes`' );
        $this->db()->query( 'truncate table `socks_day_event`' );
        $this->db()->query( 'truncate table `socks_day_event_attributes`' );
        $this->db()->query( 'truncate table `socks_meta`' );
    }
    
    function tearDown()
    {
       $this->db()->rollback();
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
            ->from( 'socks_event');
        return $select->query( Zend_Db::FETCH_OBJ );
    }
    
    protected function logHour( $timeParts, $attributes = array(), $type = 'click', $timesToLogIt = 1, $hostname = null )
    {
        $sampleData = new SampleData;
        $hour = isset($timeParts['hour']) ? $timeParts['hour'] : 1;
        $sampleData->logHit( $hour, $this->minute(), $this->second(), $timeParts['day'], $timeParts['month'], $timeParts['year'], $timesToLogIt, $attributes, $type, $hostname );
    }
    
    protected function logHourDeprecated( $hour, $day, $month, $year, $times, $attributes = array(), $type = 'click', $hostname = null )
    {
        $sampleData = new SampleData;
        $sampleData->logHit( $hour, $this->minute(), $this->second(), $day, $month, $year, $times, $attributes, $type, $hostname );
    }
    
    protected function minute()
    {
        return rand(1,59);
    }
    
    protected function second()
    {
        return rand(1,59);
    }
}