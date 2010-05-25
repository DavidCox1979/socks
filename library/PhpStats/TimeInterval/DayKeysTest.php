<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayKeysTest extends PhpStats_TimeInterval_DayTestCase
{
	function testNoAttributesWhenUncompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array(), $day->describeAttributeKeys() );
    }

    function testNoAttributesWhenCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array(), $day->describeAttributeKeys() );
    }
    
    function testWhenUncompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }

    function testWhenCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'when compacted, returns array of distinct attribute keys in use' );
    }
    
    function testWhenChildrenCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compactChildren();
        
		$day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'when children compacted, returns array of distinct attribute keys in use' );
    }
    
    function testWhenSomeChildrenCompacted()
    {
		$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array(), false );
        $hour->compact();
        
        $this->clearUncompactedEvents(true);
        
		$day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'when some children compacted, returns array of distinct attribute keys in use' );
    }
    
    function testUncompactedHitsDisabled() 
    {
		$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeAttributeKeys(), 'when uncompacted hits are disabled, describeAttributeKeys should return empty array' );
    }
    
    function testEventType()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('b' => 1 ), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys('eventA'), 'returns array of distinct attribute keys in use (for specific event type)' );
    }
    
    function testEventType2()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('b' => 1 ), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('b'), $day->describeAttributeKeys('eventB'), 'returns array of distinct attribute keys in use (for specific event type)' );
    }
    
    function testHoursCompacted()
    {
		$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compactChildren();
        
        $this->clearUncompactedEvents( true );
        $this->assertEquals( array('a'), $day->describeAttributeKeys() );
    }
    
    function testPresent()
    {
        $this->logHour( $this->now(), array('a' => 1 ), 'eventA', self::COUNT );
        $this->logHour( $this->now(), array('a' => 2 ), 'eventA', self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->now(), array(), false );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct keys attributes in use (when time interval is "now")' );
    }
    
    function testExcludesDifferentDay()
    {
    	$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'b' => 1 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different days from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testExcludesDifferentDayCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayPlusOneDayTimeParts() );
        $day->compact();
        
        $this->clearUncompactedEvents();
        $day = new PhpStats_TimeInterval_Day( $this->dayPlusOneDayTimeParts() );
        $this->assertEquals( array('b'), $day->describeAttributeKeys(), 'excludes different days from describeAttributeKeys() (compacted)' );
    }
    
    function testExcludesDifferentMonth()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'b' => 1 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );                                                  
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different months from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testExcludesDifferentMonthCompacted()
    {
    	$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'b' => 1 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false ); 
        
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different months from describeAttributeKeys() (compacted)' );
    }
    
    function testExcludesDifferentYear()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'b' => 1 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different years from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testExcludesDifferentYearCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'b' => 1 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different years from describeAttributeKeys() (compacted)' );
	}
	
	function testWhenUncompacted_ShoulfFilterByEventType()
    {
        return $this->markTestIncomplete();
    }
    
    function testWhenCompacted_ShouldFilterByEventType()
    {
        return $this->markTestIncomplete();
    }
    
    function testWhenChildrenCompacted_ShouldFilterByEventType()
    {
    	return $this->markTestIncomplete();
	}
}