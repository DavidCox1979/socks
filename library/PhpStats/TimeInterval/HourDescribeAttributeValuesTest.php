<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourDescribeAttributeValuesTest extends PhpStats_TimeInterval_HourTestCase
{
    function testWhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->describeAttributesValues(), 'when [hour] is not compacted, should return array of distinct keys & their values' );
    }
    
    function testWhenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->describeAttributesValues(), 'when [hour] is compacted, should return array of distinct keys & their values' );
    }
    
    function testUncompactedHitsDisabled() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->clearUncompactedEvents();
        
        $this->assertEquals( array(), $hour->describeAttributesValues(), 'when uncompacted hits are disabled, and not compacted, describeAttributeValues should return empty array' );
    }
    
    function testExcludesDifferentHours()
    {
        $this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different hours');
    }
    
    function testExcludesDifferentHoursCompacted()
    {
		$this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different hours (compacted)');
    }
    
    function testExcludesDifferentDays()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different days');
    }
    
    function testExcludesDifferentDaysCompacted()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different days (compacted)');
    }
    
    function testExcludesDifferentMonths()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different months');
    }
    
    function testExcludesDifferentMonthsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different months (compacted)');
    }
    
    function testExcludesDifferentYears()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different years');
    }
    
    function testExcludesDifferentYearsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different years (compacted)');
    }
    
    function testWhenCompacted_ShouldFilterByEventType()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1 ) ), $hour->describeAttributesValues( 'typeA'), 'describing attribute values for specific event type should return values only for that type');
    }
    
    function testDoDescribeAttributeValues()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 2 ), $hour->describeSingleAttributeValues('b'), 'describes attribute values for a single attribute' );
    }
    
    function testConstrainByAnotherAttributeUnCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $hour->describeSingleAttributeValues('a'), 'when uncompacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 2 ) );
        $this->assertEquals( array( 2, 3 ), $hour->describeSingleAttributeValues('a'), 'when compacted should constrain attribute values by other attributes' );
    }
    
}