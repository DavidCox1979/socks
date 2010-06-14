<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthCompactTest extends PhpStats_TimeInterval_DayTestCase
{    
    function testShouldCompactSpecicEventType()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype' );
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype'), 'should get compacted count for specific event type' );
    }
    
    function testShouldCompactAllEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventtype1' );
        $this->logThisDayWithHour( 1, array(), 'eventtype2' );
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT*2, $month->getCompactedCount(), 'should get compacted count for all event types' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedHitsDisabledCannotCompact()
    {
        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertFalse( $month->canCompact(), 'when uncompacted queries are disabled, should not compact' );
        $month->compact();
    }
    
    /**
    * @expectedException Exception
    */
    function testShouldNotCompactWhenFilteringWithAttributes()
    {
         $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ));
         $month->compact();
    }
    
    function testAttributesThruConstructor()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 2, $month->getCount('click'), 'getCompactedCount should return count only for the requested attribute (passed to constructor)' );
    }
    
    function testAttributesThruMethod()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
        
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 4, $month->getCount('click', array( 'a' => 1 ) ), 'getCompactedCount should return count only for the requested attribute (passed to method)' );
    }
    
    function testAttributesNone()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 3 ) );
        $this->assertEquals( 0, $month->getCount('click'), 'when filtering on non-existant value, count should always equal 0' );
    }
    
    function testNullMeansAll()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => null ) );
        $this->assertEquals( 2, $month->getCount('click'), 'passing null for an attribute is the same as not passing it' );
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
    
    function testHasBeenCompactedWithZeroHits()
    {
        $month = $this->getMonth();
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertTrue( $month->hasBeenCompacted() );
    }
    
    function testHasBeenCompactedWithAttribs()
    {
    	$this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventtype' );
        $month = $this->getMonth();
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertTrue( $month->hasBeenCompacted() );
    }
    
    function testHasNotBeenCompacted()
    {
        $month = $this->getMonth();
        
        $month = $this->getMonth();
        $this->assertFalse( $month->hasBeenCompacted() );
    }
    
    function testHasNotBeenCompacted2()
    {
    	$timeParts = $this->getTimeParts();
        $timeParts['day'] = 1;
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $day->compact();
        $month = new PhpStats_TimeInterval_Month( $timeParts );
        $this->assertFalse( $month->hasBeenCompacted() );
    }
    
    function testHasBeenCompacted2()
    {
    	$timeParts = $this->getTimeParts();
        $timeParts['day'] = 1;
        $month = new PhpStats_TimeInterval_Month( $timeParts );
        $month->compact();
        
        $month = new PhpStats_TimeInterval_Month( $timeParts );
        $this->assertTrue( $month->hasBeenCompacted() );
    }
    
    function testClearsPreviouslyCompacted()
    {
    	$this->logThisDayWithHour( 1, array(), 'eventtype' );
        $month = $this->getMonth();
        $month->compact();
        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype') );
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT, $month->getCompactedCount('eventtype'), 'Compact() clears previously compacted' );
    }
    
    function testExcludesDifferentEventType()
    {
    	$this->logThisDayWithHour( 1, array(), 'differentType' );
        $month = $this->getMonth();
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertEquals( 0, $month->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }

    function testChildrenDays()
    {
    	$this->logHour( $this->getTimeParts() );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click') );
        
        $month->compact();
        
        $month = $this->getMonth();
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'compacting the month should cause it\'s days to be first compacted' );
    }    
    
    function testEventTypesAndAttribs()
    {
        $this->logThisDayWithHour( 1, array( 'a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array( 'b' => 2 ), 'eventB' );
        
        $month = $this->getMonth();
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $month->getCount('eventA'), 'month should compact event_types seperately when there are attributes' );
    }
    
    function testAutomaticMode()
    {
        return $this->markTestIncomplete();
    }
    
    function testNonUniquesProperly()
    {
        $oneOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
        $this->logHour( $oneOClock, array( 'a' => 1 ), 'click', self::COUNT, '127.0.0.1' );
        
        $twoOClock = array( 'hour'=>1, 'day'=>self::DAY, 'month'=>self::MONTH, 'year'=>self::YEAR );
        $this->logHour( $twoOClock, array( 'a' => 2 ), 'click', self::COUNT, '127.0.0.2' );
        
        $month = new PhpStats_TimeInterval_Month( array( 'month'=>self::MONTH, 'year'=>self::YEAR ) );
        $month->compact();
        
        $month = $this->getMonth();
        $this->assertEquals( self::COUNT * 2, $month->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
    }
    
    function testAttributes()
    {
    	return $this->markTestIncomplete();
//        $this->logThisDayWithHour( 1, array( 'a' => 1 ) );
//        $this->logThisDayWithHour( 1, array( 'a' => 2 ) );
//        
//        $month = $this->getMonth();
//        $month->compact();
//        $this->clearUncompactedEvents();
//        $month = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
//        
//        $this->assertEquals( self::COUNT, $month->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }  
    
    protected function getMonth()
	{
		return new PhpStats_TimeInterval_Month( $this->getTimeParts() );
	}
	
	protected function clearUncompactedEvents(  )
    {
	    $this->db()->query('truncate table `socks_day_event`');
	    $this->db()->query('truncate table `socks_hour_event`');
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }
}