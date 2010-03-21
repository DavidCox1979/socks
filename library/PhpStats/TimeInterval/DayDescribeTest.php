<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayDescribeTest extends PhpStats_TimeInterval_DayTestCase
{    
    function testDescribeEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $day->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testDescribeEventTypesCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'eventA', 'eventB' ), $day->describeEventTypes(), 'returns array of distinct event types in use (compacted)' );
    }
    
    function testDescribeEventTypesUncompactedHitsDisabled() 
    {
    	$this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->clearUncompactedEvents();
        $this->assertEquals( array(), $day->describeEventTypes(), 'when uncompacted hits are disabled, and day is not compacted, describeEventTypes should return empty array.' );
    }
    
    function testDescribeEventTypesUncompactedHitsDisabled2() 
    {
    	$this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array(), false, false );
        $hour->compact();
        
        $this->assertEquals( array(), $day->describeEventTypes(), 'when uncompacted hits are disabled, and day is not compacted, describeEventTypes should return empty array (even if an hour is compacted).' );
    }
    
    function testDescribeAttributeKeys()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }

    function testDescribeAttributeKeysCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeKeysUncompactedHitsDisabled() 
    {
		$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeAttributeKeys(), 'when uncompacted hits are disabled, describeAttributeKeys should return empty array' );
    }
    
    function testDescribeAttributeKeysEventType()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('b' => 1 ), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys('eventA'), 'returns array of distinct attribute keys in use (for specific event type)' );
    }
    
    function testDescribeAttributeKeysEventType2()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('b' => 1 ), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('b'), $day->describeAttributeKeys('eventB'), 'returns array of distinct attribute keys in use (for specific event type)' );
    }
    
    function testDescribeAttributeKeysHoursCompacted()
    {
		$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        foreach( $day->getHours() as $hour )
        {
			$hour->compact();
        }
        $this->clearUncompactedEvents( true );
        $this->assertEquals( array('a'), $day->describeAttributeKeys() );
    }
    
    function testDescribeAttributeValues()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesUncompactedHitsDisabled() 
    {
    	$this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeAttributesValues(), 'when uncompacted hits are disabled, values for attributes in use should be empty' );
    }

    function testDescribeAttributeValuesOmitsDifferentDay()
    {
        $this->logHour( $this->dayPlusOneTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day');
    }
    
    function testDescribeAttributeValuesOmitsDifferentDayCompacted()
    {
        $this->logHour( $this->dayPlusOneTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day (compacted)');
    }
    
    function testDescribeAttributeValuesOmitsDifferentDayCompactedHours()
    {
        $this->logHour( $this->dayPlusOneTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        foreach( $day->getHours() as $hour )
    	{
			$hour->compact();
    	}
        $this->clearUncompactedEvents( true );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different day (compacted)');
    }
        
    function testDescribeAttributeValuesOmitsDifferentMonth()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month');
    }
    
    function testDescribeAttributeValuesOmitsDifferentMonthCompacted()
    {
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different month (compacted)');
    }
    
    function testDescribeAttributeValuesOmitsDifferentYear()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year');
    }
    
    function testDescribeAttributeValuesOmitsDifferentYearCompacted()
    {
        $this->logHour( $this->dayPlusOneYearTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->dayTimeParts(), array( 'a' => 2 ) );
        $day = new PhpStats_TimeInterval_Day( $this->dayTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $day->describeAttributesValues(), 'describing attribute values should omit values from different year (compacted)');
    }
    
    function testDescribeAttributeValuesSpecificEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1 ) ), $day->describeAttributesValues( 'typeA'), 'describing attribute values for specific event type should return values only for that type');
    }
    
    function testDescribeAttributeKeysPresent()
    {
        $timeParts = $this->now();
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct keys attributes in use' );
    }
    
    function testDescribeAttributeValuesPresent()
    {
        $timeParts = $this->now();
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use (when time interval is "now")' );
    }
    
    function testDescribeAttributeValuesNoAutoCompact()
    {
        $timeParts = $this->now();
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $timeParts, array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use (non auto-compact mode)' );
    }
    
    function testDescribeEventTypesExcludesDifferentDays()
    {
        $this->logHourDeprecated( 1, 1, 1, 2002, self::COUNT, array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => 2,
            'month' => 1,
            'year' => 2002
        ));
        $this->assertEquals( array(), $day->describeEventTypes(), 'excludes different time interavals from describeEventTypes()' );
    }
    
    function testDescribeEventTypesExcludesDifferentMonths()
    {
		return $this->markTestIncomplete(); 
    }
    
    function testDescribeEventTypesExcludesDifferentYears()
    {
		return $this->markTestIncomplete(); 
    }
    
    function testDescribeAttributeKeysExcludesDifferentDay()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY + 1, self::MONTH, self::YEAR, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different days from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testDescribeAttributeKeysExcludesDifferentDayCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY + 1, self::MONTH, self::YEAR, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different days from describeAttributeKeys() (compacted)' );
    }
    
    function testDescribeAttributeKeysExcludesDifferentMonth()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH + 1, self::YEAR, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
                                                                                                       
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different months from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testDescribeAttributeKeysExcludesDifferentMonthCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH + 1, self::YEAR, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different months from describeAttributeKeys() (compacted)' );
    }
    
    function testDescribeAttributeKeysExcludesDifferentYear()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR + 1, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different years from describeAttributeKeys()' );
    }
    
    /** @todo also test for when hours are compacted? */
    function testDescribeAttributeKeysExcludesDifferentYearCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR + 1, self::COUNT, array( 'b' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), array(), false );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'excludes different years from describeAttributeKeys() (compacted)' );
	}
	 
    protected function dayPlusOneTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOne = $day;
        $dayPlusOne['day'] += 1;
        return $dayPlusOne;
    }
    
    protected function dayPlusOneMonthTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOneMonth = $day;
        $dayPlusOneMonth['month'] += 1;
        return $dayPlusOneMonth;
    }
    
    protected function dayPlusOneYearTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOneYear = $day;
        $dayPlusOneYear['year'] += 1;
        return $dayPlusOneYear;
    }
    
	protected function dayTimeParts()
    {
		$day = array(
        	'hour' => 1,
        	'day' => self::DAY,
        	'month' => self::MONTH,
        	'year' => self::YEAR
        );
        return $day;
    }
}