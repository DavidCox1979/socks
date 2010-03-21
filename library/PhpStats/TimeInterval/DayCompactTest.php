<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayCompactTest extends PhpStats_TimeInterval_DayTestCase
{    
    function testCompact()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype'), 'Compacts it\'s count' );
    }         
    
    function testCompactIsRepeatable()
    {
        $this->logThisDayWithHour( 1, array(),  'eventA' );
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        $day->compact();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventA'), 'calling compact after an interval has been compacted should do nothing' );
    }
    
    function testHasBeenCompactedWithNoTraffic()
    {
        $day = $this->getDay();
        $day->getCount( 'click' );
        $this->assertTrue( $day->hasBeenCompacted() );
    }
    
    function testHasBeenCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->assertTrue( $day->hasBeenCompacted() );
    }
    
    function testHasBeenCompactedWithAttribs()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->assertTrue( $day->hasBeenCompacted() );
    }
    
    function testHasNotBeenCompacted()
    {
        $day = $this->getDay();
        $this->assertFalse( $day->hasBeenCompacted() );
    }
    
    function testHasNotBeenCompacted2()
    {
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertFalse( $day->hasBeenCompacted() );
    }
    
    function testCompactClearsPreviouslyCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype') );
        $day->compact();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype'), 'Compact() clears previously compacted' );
    }
    
    function testCompactedCountExcludesDifferentEventType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( 0, $day->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }

    function testCompactsChildHours()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
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
        $this->assertEquals( self::COUNT * 4, $day->getCount('click'), 'compact the data' );
        
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
    
    function testShouldCompactAutomatically()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->getCount('click');
        $this->clearUncompactedEvents();
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should compact automatically' );
    }
    
    function testCompactsNonUniquesProperly()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.2' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->assertEquals( self::COUNT * 2, $day->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
    }
}