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
    
    function testDescribeAttributeKeys()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
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
    
    function testDescribeAttributeKeysCompacted()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeValues()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesCompactedPast()
    {
        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesPresent()
    {
        $timeParts = $this->now();
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesNoAutoCompact()
    {
        $timeParts = $this->now();
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $timeParts, array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesBeforeAndAfterCompacting()
    {
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 1 ), 'eventA' );
        $day = new PhpStats_TimeInterval_Day( $this->now() );
        $day->getCount( 'eventA' );
        $this->logHourDeprecated( date('G'), date('j'), date('n'), date('Y'), self::COUNT, array('a' => 2 ), 'eventA' );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'when logging hits for new attribs after generating report new attribs should get picked up.' );
    }
    
    function testDescribeEventTypesExcludesDifferentTimeIntervals()
    {
        $this->logHourDeprecated( 1, 1, 1, 2002, self::COUNT, array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => 2,
            'month' => 1,
            'year' => 2002
        ));
        $this->assertEquals( array(), $day->describeEventTypes(), 'excludes different time interavals from describeEventTypes()' );
    }
    
    function testDescribeAttributeKeysExcludesDifferentDayCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeKeysExcludesDifferentMonthCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeKeysExcludesDifferentYearCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeKeysExcludesDifferentDayUncompacted()
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
    
    function testDescribeAttributeKeysExcludesDifferentMonthUncompacted()
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
    
    function testDescribeAttributeKeysExcludesDifferentYearUncompacted()
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
    
    function testDescribeAttributeExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }   
}