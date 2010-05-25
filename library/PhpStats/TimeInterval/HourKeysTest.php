<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourKeysTest extends PhpStats_TimeInterval_HourTestCase
{
    function testWhenUncompacted_ShouldReturnDistinctKeys()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys(), 'when uncompacted, returns array of distinct attribute keys' );
    }
    
    function testWhenCompacted_ShouldReturnDistinctKeys() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array() );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys(), 'when compacted, returns array of distinct attribute keys (compacted)' );
    }
    
    function testWhenUnCompactedDisabled_ShouldReturnEmpty() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $hour->describeAttributeKeys(), 'when unCompacted hits disabled, and autoCompact disabled, describeAttributeKeys should return empty array' );
    }
    
    function testWhenUncompacted_ShouldOmitDifferentHours()
    {
        $this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when uncompacted, describing keys should omit different hours' );
    }
    
    function testWhenCompacted_ShouldOmitDifferentHours()
    {
		$this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when compacted, describing keys should omit different hours' );
    }
    
    function testWhenUncompacted_ShouldOmitDifferentDays()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when uncompacted, describing keys should omit different days' );
    }
    
    function testWhenCompacted_ShouldOmitDifferentDays()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when compacted, describing keys should omit different days' );
    }
    
    function testWhenUncompacted_ShouldOmitDifferentMonths()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when uncompacted, describing keys should omit different months' );
    }
    
    function testWhenCompacted_ShouldOmitDifferentMonths()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when compacted, describing keys should omit different months' );
    }
    
    function testWhenUncompacted_ShouldOmitDifferentYears()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when uncompacted, describing keys should omit different years' );
    }
    
    function testWhenCompacted_ShouldOmitDifferentYears()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'when compacted, describing keys should omit different years' );
    }
    
    function testWhenDescribeSpecificEvent_ShouldOmitOtherEvents()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'eventA' );
        $this->logHour( $this->getTimeParts(), array( 'b' => 1 ), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys('eventA'), 'when specific event type is passed, should omit events of other types' );
    }
    
}