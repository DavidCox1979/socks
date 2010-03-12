<?php
class HourCompactorMultipleEventTypesTest extends PHPUnit_Extensions_PerformanceTestCase
{
    
    const HOUR = 1;
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2002;
    
    function setUp()
    {
        $this->db()->query( 'truncate table `socks_event`' );
        $this->db()->query( 'truncate table `socks_event_attributes`' );
        $this->db()->query( 'truncate table `socks_hour_event`' );
        $this->db()->query( 'truncate table `socks_hour_event_attributes`' );
        $this->db()->query( 'truncate table `socks_day_event`' );
        $this->db()->query( 'truncate table `socks_day_event_attributes`' );
        $this->db()->query( 'truncate table `socks_meta`' );
        
        $sampleData = new SampleData();
        $sampleData->logHit( self::HOUR, 1, 1, self::DAY, self::MONTH, self::YEAR, 1, array(
            'location' => 'marina_1'
        ), 'featured_impression');
        $sampleData->logHit( self::HOUR, 1, 1, self::DAY, self::MONTH, self::YEAR, 1, array(
            'location' => 'marina_2'
        ), 'featured_impression');
        $sampleData->logHit( self::HOUR, 1, 1, self::DAY, self::MONTH, self::YEAR, 1, array(
            'location' => 'marina_3'
        ), 'featured_impression');
        $sampleData->logHit( self::HOUR, 1, 1, self::DAY, self::MONTH, self::YEAR, 1, array(
            'location' => 'inlet_1'
        ), 'featured_impression');
        $sampleData->logHit( self::HOUR, 1, 1, self::DAY, self::MONTH, self::YEAR, 1, array(
            'location' => 'lighthouse_1'
        ), 'featured_impression');
    }
    
    function testCompactsForHour()
    {
        $profiler = Zend_Registry::get('db')->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        
        $this->setMaxRunningTime(15);
        $hour = $this->getHour();
        $hour->compact();
        
        $this->assertLessThan( 35, $profiler->getTotalNumQueries() );
        $profiler->setEnabled(false);
    }
    
    protected function getHour()
    {
        return new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}