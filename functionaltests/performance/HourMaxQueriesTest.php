<?php
class HourMaxQueriesTest extends PHPUnit_Extensions_PerformanceTestCase
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
        
    }
    
    function testCompactMaxQueries()
    {
        $this->dataForDay( self::HOUR, self::DAY, self::MONTH, self::YEAR );
        
        $profiler = $this->db()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled( true );
        
        $hour = $this->getHour();
        $hour->compact();
        $queries = $profiler->getTotalNumQueries();
        $this->assertLessThan( 10, $queries );
        $profiler->setEnabled( false );
    }    
    
    function testHasBeenCompactedMaxQueries()
    {
        $this->dataForDay( self::HOUR, self::DAY, self::MONTH, self::YEAR );
        
        $profiler = $this->db()->getProfiler();
         
        $hour = $this->getHour();
        $profiler->clear();
        $profiler->setEnabled( true );
        
        $hour->hasBeenCompacted();
        $hour->hasBeenCompacted();
        $hour->hasBeenCompacted();
        $queries = $profiler->getTotalNumQueries();
        $this->assertEquals( 1, $queries );
        
        $profiler->setEnabled( false );
    }
    
    function testCountWithAttribsMaxQueries()
    {
        $this->dataForDayWithAttribs( self::HOUR, self::DAY, self::MONTH, self::YEAR );
        
        $profiler = $this->db()->getProfiler();
         
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        $profiler->clear();
        $profiler->setEnabled( true );
        
        $hour->getCount('click');
        $queries = $profiler->getTotalNumQueries();
        $this->assertEquals( 2, $queries );
        
        $profiler->setEnabled( false );
        
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
    
    public function dataForDay( $hour, $day, $month, $year, $multiplierFactor = 2 )
    {
        $sampleData = new SampleData();
        for( $minute = 1; $minute <= 59; $minute+= 10 )
        {
            for( $second = 1; $second <= 59; $second+= 30 )
            {
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, $multiplierFactor, array(), 'click' );
            }
        }
    }
    
    public function dataForDayWithAttribs( $hour, $day, $month, $year, $multiplierFactor = 2 )
    {
        $sampleData = new SampleData();
        for( $minute = 1; $minute <= 59; $minute+= 10 )
        {
            for( $second = 1; $second <= 59; $second+= 30 )
            {
                $attribs = array( 'attribute1' => rand( 1,1), 'attribute2' => rand( 1,1), 'attribute3' => rand(1,1) );
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, $multiplierFactor, $attribs, 'click' );
            }
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}