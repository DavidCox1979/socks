<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthCompactTest extends PhpStats_TimeInterval_DayTestCase
{    
    function testCompactSpecicEventType()
    {

        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype'), 'gets compacted count for specific event type' );
    }
    
    function testCompactAllEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype1' );
        $this->logThisDayWithHour( 1, array(), 'eventtype2' );
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT*2, $month->getCompactedCount(), 'gets compacted count for all event types' );
    }
    
    function testCompact2()
    {
        $this->logThisDayWithHour( 1 );
        $this->logThisDayWithHour( 11 );
        $this->logThisDayWithHour( 13 );
        $this->logThisDayWithHour( 23 );
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT * 4, $month->getCount('click'), 'compact the data' );
        $this->clearUncompactedEvents();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT * 4, $month->getCount('click'), 'compacting the month should sum up the values for it\'s children days and compact them at the "grain" of month_event' );
    }
    
    function testCompactIsRepeatable()
    {
        $this->logThisDayWithHour( 1, array(),  'eventA' );
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventA'), 'calling compact after an interval has been compacted should do nothing' );
    }
    
    function testHasBeenCompactedWithNoTraffic()
    {
        $month = $this->getMonth();
        $month->getCount( 'click' );
        $this->assertTrue( $month->hasBeenCompacted() );
    }
    
    function testHasBeenCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $month = $this->getMonth();
        $month->compact();
        $this->assertTrue( $month->hasBeenCompacted() );
    }
//    
//    function testHasBeenCompactedWithAttribs()
//    {
//        $this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventtype' );
//        $month = $this->getMonth();
//        $month->compact();
//        $this->assertTrue( $month->hasBeenCompacted() );
//    }
//    
//    function testHasNotBeenCompacted()
//    {
//        $month = $this->getMonth();
//        $this->assertFalse( $month->hasBeenCompacted() );
//    }
//    
//    function testHasNotBeenCompacted2()
//    {
//        $timeParts = $this->getTimeParts();
//        $timeParts['hour'] = 1;
//        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
//        $hour->compact();
//        $month = new PhpStats_TimeInterval_Day( $timeParts );
//        $this->assertFalse( $month->hasBeenCompacted() );
//    }
//    
//    function testHasBeenCompacted2()
//    {
//        $timeParts = $this->getTimeParts();
//        $timeParts['hour'] = 1;
//        $month = new PhpStats_TimeInterval_Day( $timeParts );
//        $month->compact();
//        
//        $month = new PhpStats_TimeInterval_Day( $timeParts );
//        $this->assertTrue( $month->hasBeenCompacted() );
//    }
//    
//    function testClearsPreviouslyCompacted()
//    {
//        $this->logThisDayWithHour( 1, array(), 'eventtype' );
//        $month = $this->getMonth();
//        $month->compact();
//        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype') );
//        $month->compact();
//        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype'), 'Compact() clears previously compacted' );
//    }
//    
//    function testExcludesDifferentEventType()
//    {
//        $this->logThisDayWithHour( 1, array(), 'differentType' );
//        $month = $this->getMonth();
//        $month->compact();
//        $this->assertEquals( 0, $month->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
//    }

//    function testChildrenHours()
//    {
//        $this->logHour( $this->getTimeParts() );
//        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
//        
//        $hours = $month->getHours();
//        $this->assertEquals( 1, $hours[1]->getCount('click') );
//        
//        $month->compact();
//        
//        $month = $this->getMonth();
//        $hours = $month->getHours();
//        $this->assertEquals( 1, $hours[1]->getCount('click'), 'compacting the day should cause it\'s hours to be first compacted' );
//    }    
//    
//    function testAttributes()
//    {
//        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
//        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
//        
//        $month = $this->getMonth();
//        $month->compact();
//        $this->clearUncompactedEvents();
//        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
//        
//        $this->assertEquals( self::COUNT, $month->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
//    } 
//    
//    function testEventTypesAndAttribs()
//    {
//        $this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventA' );
//        $this->logThisDayWithHour( 1, array( 'b' => 2 ), 'eventB' );
//        
//        $month = $this->getMonth();
//        $month->compact();
//        $this->clearUncompactedEvents();
//        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
//        
//        $this->assertEquals( self::COUNT, $month->getCount('eventA'), 'day should compact event_types seperately when there are attributes' );
//    } 
//    
//    function testAutomaticMode()
//    {
//        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
//        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
//        
//        $month = $this->getMonth();
//        $month->getCount('click');
//        $this->clearUncompactedEvents();
//        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
//        $this->assertEquals( self::COUNT, $month->getCount('click'), 'should compact automatically' );
//    }
//    
//    function testNonUniquesProperly()
//    {
//    	$oneOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
//        $this->logHour( $oneOClock, array( 'a' => 1 ), 'click', self::COUNT, '127.0.0.1' );
//        
//        $twoOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
//        $this->logHour( $twoOClock, array( 'a' => 2 ), 'click', self::COUNT, '127.0.0.2' );
//        
//        $wholeDay = array( 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
//        $month = new PhpStats_TimeInterval_Day( $wholeDay );
//        $month->compact();
//        
//        $this->assertEquals( self::COUNT * 2, $month->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
//    }
//    
//    /**
//    * @expectedException Exception
//    */
//    function testWhenUncomapctedHitsDisabledCannotCompact()
//    {
//		$month = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
//        $month->compact();
//    }

	protected function getMonth()
	{
		return new PhpStats_TimeInterval_Month( $this->getTimeParts() );
	}
	
	protected function clearUncompactedEvents(  )
    {
	    $this->db()->query('truncate table `socks_day_event`');
	    $this->db()->query('truncate table `socks_day_event_attributes`');
	    $this->db()->query('truncate table `socks_hour_event`');
	    $this->db()->query('truncate table `socks_hour_event_attributes`');
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }
}
