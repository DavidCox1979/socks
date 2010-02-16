<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayTest extends PhpStats_TimeInterval_TestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testShouldCountSameDay()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'should count hits of same day (different hours)' );
    }
    
    function testShouldOmitHitsFromDifferentYear()
    {
        $this->logThisDayWithHour( 1 );
        $this->insertHitDifferentYear();
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testShouldOmitHitsFromDifferentMonth()
    {
        $this->logThisDayWithHour( 1 );
        $this->insertHitDifferentMonth();
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = $this->getDay();
        $this->assertEquals( 0, $day->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testAttribute1()
    {
        $attributes = array( 'a' => 1 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 1' );
    }
    
    function testAttribute2()
    {
        $attributes = array( 'a' => 2 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 2' );
    }
    
    function testDescribeEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $day->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testDescribeAttributeKeys()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeValues()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testGetHours1()
    {
        $this->logThisDayWithHour( 1 );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHours2()
    {
        $this->logThisDayWithHour( 2 );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHoursAttribute()
    {
        $this->logThisDayWithHour( 2, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 2, array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'children hours should be filtered by same attributes we specified for the day (uncompacted)' );
    }
    
    function testGetHoursAttributeCompacted()
    {
        $this->logThisDayWithHour( 2, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 2, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        
        $this->assertEquals( self::COUNT + self::COUNT, $day->getCount('click') );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'children hours should be filtered by same attributes we specified for the day (compacted)' );
    }
    
    function testCompact()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype'), 'Compacts it\'s count' );
    }
    
    function testCompactedCountDoesntCountDifferentType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( 0, $day->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }

    function testCompactsChildHours()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click') );
        
        $day->compact();
        
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'compacting the day should cause it\'s hours to be first compacted' );
    }    
    
    function testCompactsHoursIntoDay()
    {
        $this->logThisDayWithHour( 1 );
        $this->logThisDayWithHour( 11 );
        $this->logThisDayWithHour( 13 );
        $this->logThisDayWithHour( 23 );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 4, $day->getCount('click') );
        
        $day->compact();
        
        $this->clearUncompactedEvents();
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 4, $day->getCount('click'), 'compacting the day should sum up the values for it\'s children hours and compact them at the "grain" of day_event' );
    }
    
    function testCompactsAttributes()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    } 
    
    function testDayLabel()
    {
        $day = $this->getDay();
        $this->assertEquals( 'Saturday, January 1, 2005', $day->dayLabel() );
    }
    
    function testShortDayLabel()
    {
        $day = $this->getDay();
        $this->assertEquals( '1', $day->dayShortLabel() );
    }
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( $this->getTimeParts() );
    }

    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }
 
    protected function insertHitDifferentMonth()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }   
    
    protected function clearUncompactedEvents()
    {
        $this->db()->query('truncate table `socks_hour_event`'); // delete the records from the event table to force it to read from the hour_event table. 
        $this->db()->query('truncate table `socks_event`'); // delete the records from the event table to force it to read from the hour_event table. 
    }
    
    protected function logThisDayWithHour( $hour, $attributes = array(), $eventType = 'click' )
    {
        $this->logHour( $hour, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes, $eventType );
    }
    
}
