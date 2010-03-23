<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourDescribeAttributeValuesTest extends PhpStats_TimeInterval_HourTestCase
{
    function testDescribeAttributeValues()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDescribeAttributeValuesCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use (compacted)' );
    }
    
    function testDescribeAttributeValuesUncompactedHitsDisabled() 
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->clearUncompactedEvents();
        
        $this->assertEquals( array(), $hour->describeAttributesValues(), 'when uncompacted hits are disabled, and not compacted, describeAttributeValues should return empty array' );
    }
    
    function testDescribeAttributeValuesOmitsDifferentHours()
    {
        $this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different hours');
    }
    
    function testDescribeAttributeValuesOmitsDifferentHoursCompacted()
    {
		$this->logHour( $this->timePartsPlusOneHour(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different hours (compacted)');
    }
    
    function testDescribeAttributeValuesOmitsDifferentDays()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different days');
    }
    
    function testDescribeAttributeValuesOmitsDifferentDaysCompacted()
    {
		$this->logHour( $this->timePartsPlusOneDay(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different days (compacted)');
    }
    
    function testDescribeAttributeValuesOmitsDifferentMonths()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different months');
    }
    
    function testDescribeAttributeValuesOmitsDifferentMonthsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneMonth(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different months (compacted)');
    }
    
    function testDescribeAttributeValuesOmitsDifferentYears()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different years');
    }
    
    function testDescribeAttributeValuesOmitsDifferentYearsCompacted()
    {
		$this->logHour( $this->timePartsPlusOneYear(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array('a' => array( 2 ) ), $hour->describeAttributesValues(), 'describing attribute values should omit values from different years (compacted)');
    }
    
    function testDescribeAttributeValuesSpecificEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1 ) ), $hour->describeAttributesValues( 'typeA'), 'describing attribute values for specific event type should return values only for that type');
    }
    
    function testDoDescribeAttributeValues()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 2 ), $hour->doGetAttributeValues('b'), 'describes attribute values for a single attribute' );
    }
    
}