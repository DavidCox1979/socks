<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayCompactTest extends PhpStats_TimeInterval_DayTestCase
{    
    function testCompactSpecicEventType()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype'), 'gets compacted count for specific event type' );
    }
    
    function testCompactAllEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype1' );
        $this->logThisDayWithHour( 1, array(), 'eventtype2' );
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT*2, $day->getCompactedCount(), 'gets compacted count for all event types' );
    }
    
    function testCompact2()
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
    
    function testCompactIsRepeatable()
    {
        $this->logThisDayWithHour( 1, array(),  'eventA' );
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        $day->compact();
        
        $day = $this->getDay();
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
    
    function testHasBeenCompacted2()
    {
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $day->compact();
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted() );
    }
    
    function testClearsPreviouslyCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype') );
        $day->compact();
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCompactedCount('eventtype'), 'Compact() clears previously compacted' );
    }
    
    function testExcludesDifferentEventType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = $this->getDay();
        $day->compact();
        
        $day = $this->getDay();
        $this->assertEquals( 0, $day->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }

    function testChildrenHours()
    {
        $this->logHour( $this->getTimeParts() );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        
        $hours = $day->getHours();
        $this->assertEquals( 1, $hours[1]->getCount('click') );
        
        $day->compact();
        
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( 1, $hours[1]->getCount('click'), 'compacting the day should cause it\'s hours to be first compacted' );
    }    
    
    function testAttributes()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    } 
    
    function testEventTypesAndAttribs()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array( 'b' => 2 ), 'eventB' );
        
        $day = $this->getDay();
        $day->compact();
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $day->getCount('eventA'), 'day should compact event_types seperately when there are attributes' );
    } 
    
    function testAutomaticMode()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->getCount('click');
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should compact automatically' );
    }
    
    function testNonUniquesProperly()
    {
    	$oneOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
        $this->logHour( $oneOClock, array( 'a' => 1 ), 'click', self::COUNT, '127.0.0.1' );
        
        $twoOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
        $this->logHour( $twoOClock, array( 'a' => 2 ), 'click', self::COUNT, '127.0.0.2' );
        
        $wholeDay = array( 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
        $day = new PhpStats_TimeInterval_Day( $wholeDay );
        $day->compact();
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 2, $day->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedHitsDisabledCannotCompact()
    {
		$day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $day->compact();
    }
}
