<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayDescribeAttributeValuesTest extends PhpStats_TimeInterval_DayTestCase
{
	function testDescribe()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testUncompactedHitsDisabled() 
    {
    	$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeAttributesValues(), 'when uncompacted hits are disabled, values for attributes in use should be empty' );
    }

    function testOmitsDifferentDay()
    {
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day');
    }
    
    function testOmitsDifferentDayCompacted()
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
    
    function testOmitsDifferentDayCompactedHours()
    {
        $this->logHour( $this->dayPlusOneDayTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayPlusOneDayTimeParts() );
        foreach( $day->getHours() as $hour )
    	{
			$hour->compact();
    	}
    	$day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        foreach( $day->getHours() as $hour )
    	{
			$hour->compact();
    	}
        $this->clearUncompactedEvents( true );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day (compacted)');
    }
        
    function testOmitsDifferentMonth()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month');
    }
    
    function testOmitsDifferentMonthCompacted()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month (compacted)');
    }
    
    function testOmitsDifferentYear()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year');
    }
    
    function testOmitsDifferentYearCompacted()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year (compacted)');
    }
    
    function testSpecificEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1 ) ), $day->describeAttributesValues( 'typeA'), 'describing attribute values for specific event type should return values only for that type');
    }
    
    function testPresent()
    {
        $this->logHour( $this->now(), array('a' => 1 ), 'eventA', self::COUNT );
        $this->logHour( $this->now(), array('a' => 2 ), 'eventA', self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->now() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes (when time interval is "now")' );
    }
    
    function testNoAutoCompact()
    {
        $this->logHour( $this->now(), array('a' => 1 ), 'eventA', self::COUNT );
        $this->logHour( $this->now(), array('a' => 2 ), 'eventA', self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->now(), array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes(non auto-compact mode) (when time interval is "now")' );
    }
    
    function testConstrainByAnotherAttribute()
    {
		return $this->markTestIncomplete(); 
		//return $this->fail( 'when there are hits for multiple attributes, should be able to describe attribute values WHERE the other attribute equals a certain value' );
    }
}