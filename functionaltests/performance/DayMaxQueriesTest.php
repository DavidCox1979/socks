<?php
class DayMaxQueriesTest extends PHPUnit_Extensions_PerformanceTestCase
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
        
        $this->dataForDay( self::HOUR, self::DAY, self::MONTH, self::YEAR );
    }
    
    function testDayMaxQueries()
    {
        $profiler = $this->db()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled( true );
        

        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        $day->getCount('click');
        
        $queries = $profiler->getTotalNumQueries();
		$profiler->setEnabled( false );
        $this->assertLessThan( 12, $queries );
    }
    
    /**  Could be lower if optomized childrenAreCompacted */
    function testDescribeAttributeKeys()
    {
		$profiler = $this->db()->getProfiler();
		$profiler->clear();
        $profiler->setEnabled( true );
        
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $day->describeAttributeKeys();
        $day->describeAttributeKeys();
        $day->describeAttributeKeys();
        
        $queries = $profiler->getTotalNumQueries();
        $profiler->setEnabled( false );
        $this->assertLessThan( 9, $queries );
    }
    
    function testDescribeAttributeValues()
    {
		$profiler = $this->db()->getProfiler();
		$profiler->clear();
        $profiler->setEnabled( true );
        
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $day->describeAttributesValues();
        $day->describeAttributesValues();
        $day->describeAttributesValues();
        
        $queries = $profiler->getTotalNumQueries();
        $profiler->setEnabled( false );
        $this->assertLessThan( 9, $queries );
    }
    
    
    function testDoGetAttributeValues()
    {
		$day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $profiler = $this->db()->getProfiler();
        $profiler->clear();
        $profiler->setEnabled( true );
        
        $day->doGetAttributeValues('a');
        $day->doGetAttributeValues('a');
        $day->doGetAttributeValues('a');
        
        $queries = $profiler->getTotalNumQueries();
        $profiler->setEnabled( false );
        $this->assertLessThan( 9, $queries );
    }
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( array(
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
//                $attribs = array( 'attribute1' => rand( 1,5), 'attribute2' => rand( 1,5), 'attribute3' => rand(1,5) );
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, $multiplierFactor, array(), 'click' );
            }
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}