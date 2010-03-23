<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourDescribeAttributeKeysTest extends PhpStats_TimeInterval_HourTestCase
{
    function testDescribeAttributeKeys()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testCompacted() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use (compacted)' );
    }
    
    function testUnCompactedDisabled() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $hour->describeAttributeKeys(), 'when unCompacted hits disabled, and autoCompact disabled, describeAttributeKeys should return empty array' );
    }
    
    function testOmitsDifferentHours()
    {
        $this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different hours' );
    }
    
    function testOmitsDifferentHoursCompacted()
    {
		$this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different hours (compacted)' );
    }
    
    function testOmitsDifferentDays()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different days' );
    }
    
    function testOmitsDifferentDaysCompacted()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different days (compacted)' );
    }
    
    function testOmitsDifferentMonths()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different months' );
    }
    
    function testOmitsDifferentMonthsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different months (compacted)' );
    }
    
    function testOmitsDifferentYears()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different years' );
    }
    
    function testOmitsDifferentYearsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'describing attribute keys should omit data from different years (compacted)' );
    }
    
    function testSpecificEventType()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'eventA' );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys('eventA'), 'should describe attribute keys for specific event type' );
    }

    function testOmitsDifferentTimesCompacted()
    {
        $timeParts = array(
            'hour' => self::HOUR+1,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, array( 'a' => 1 ) );
        
        $timeParts = array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, array( 'b' => 1 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR+1,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
        $hour->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
        $hour->compact();
        
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use (compacted)' );
    }
    
}