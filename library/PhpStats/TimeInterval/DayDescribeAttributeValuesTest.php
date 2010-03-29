<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayDescribeAttributeValuesTest extends PhpStats_TimeInterval_DayTestCase
{
	function testWhenUncompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'when [day] is not compacted, should return array of distinct keys & their values' );
    }
    
    function testWhenCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'when [day] is compacted, should return array of distinct keys & their values' );
    }
    
    function testWhenChildrenCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'when children [hours] are compacted, should return array of distinct keys & their values' );
    }
    
    function testUncompactedHitsDisabled() 
    {
    	$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeAttributesValues(), 'when uncompacted hits are disabled, values for attributes in use should be empty' );
    }

    function testExcludesDifferentDay()
    {
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day');
    }
    
    function testExcludesDifferentDayCompacted()
    {
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->dayPlusOneDayTimeParts() );
        $day->compact();
        
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day (compacted)');
    }
    
    function testExcludesDifferentDayChildrenCompacted()
    {
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->dayPlusOneDayTimeParts() );
        $day->compactChildren();
        
    	$day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compactChildren();
        
        $this->clearUncompactedEvents( true );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day (compacted)');
    }
        
    function testExcludesDifferentMonth()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month');
    }
    
    function testExcludesDifferentMonthCompacted()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month (compacted)');
    }
    
    function testExcludesDifferentYear()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year');
    }
    
    function testExcludesDifferentYearCompacted()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year (compacted)');
    }
    
    function testShoulfFilterByEventType_WhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $day->describeAttributesValues( 'typeA'), 'when day is uncompacted, describing attribute values for specific event type should return values only for that type');
    }
    
    function testShouldFilterByEventType_WhenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array() );
        $day->compact();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array() );
        $this->assertEquals( array('a' => array( 1 ) ), $day->describeAttributesValues( 'typeA'), 'when day is compacted, describing attribute values for specific event type should return values only for that type');
    }
    
    function testShouldFilterByEventType_WhenChildrenCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $day->describeAttributesValues( 'typeA'), 'when day\'s children hours are compacted, describing attribute values for specific event type should return values only for that type');
    }
    
    function testPresent()
    {
        $this->logHour( $this->now(), array('a' => 1 ), 'eventA', self::COUNT );
        $this->logHour( $this->now(), array('a' => 2 ), 'eventA', self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->now() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes (when time interval is "now")' );
    }
    
    function testConstrainByAnotherAttributeUnCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $day->describeSingleAttributeValues('a'), 'when uncompacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeChildrenHoursCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $day->describeSingleAttributeValues('a'), 'when children hours compacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'b' => 2 ) );
        $this->assertEquals( array( 2, 3 ), $day->describeSingleAttributeValues('a'), 'when compacted should constrain attribute values by other attributes' );
    }
    
}