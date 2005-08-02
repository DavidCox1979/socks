<?php
class PhpStats_TimeInterval_HourTest extends PhpStats_TimeIntervalTestCase
{
    const HOUR = 3;
    const DAY = 3;
    const MONTH = 3;
    const YEAR = 2005;
    
    const COUNT = 5;
    
    function testUncompactedCount()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCount should sum up additive count from the event table' );
    }
    
    function testShouldNotCountDifferntEventType()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( 0, $hour->getCount('different'), 'should not count different event type' );
    }
    
    function testShouldNotCountDifferentDay()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentDay(); // should not count this        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different day' );
    }
    
    function testShouldNotCountDifferentMonth()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentMonth(); // should not count this        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different month' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different year' );
    }
    
    function testAttribute()
    {
        $attributes = array( 'a' => 2 );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), $attributes );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'counts additive values for log events with specific attribute values' );
    }
    
    function testGetAttributes()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->getAttributes(), 'gets distinct attribute' );
    }
    
    function testGetAttributesValues()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->getAttributesValues(), 'gets distinct attribute values' );
    }
    
    function testCompactsEventsIntoHour()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click') );
        
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'compacts data about the events table into the hour_event table' );
    } 
    
    function testCompactsAttributes()
    {
        return $this->markTestIncomplete();
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    } 
    
    protected function clearUncompactedEvents()
    {
        $this->db()->query('truncate table `event`'); // delete the records from the event table to force it to read from the hour_event table. 
    }
    
    protected function getTimeParts()
    {
        return array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
    
    protected function insertHitDifferentDay()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY - 1, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
    
    protected function insertHitDifferentMonth()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }    
}