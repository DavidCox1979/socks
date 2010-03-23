<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourDescribeTest extends PhpStats_TimeInterval_HourTestCase
{
    function testFillsInNullAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( array( 'a'=>1, 'b'=>null ), $hour->getAttributes() );
    }
    
    function testFillsInNullAttributesCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'a'=>1, 'b'=>null ), $hour->getAttributes() );
    }

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
    
    function testDescribeAttributesCombinations()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        
        $combinations = array(
            array( 'a' => null, 'b' => null ),
            
            array( 'a' => '1',  'b' => null ),
            array( 'a' => '2',  'b' => null ),
            
            array( 'a' => null, 'b' => '1' ),
            array( 'a' => null, 'b' => '2' ),
            
            array( 'a' => 1,    'b' => '1' ),
            array( 'a' => 1,    'b' => '2' ),
            
            array( 'a' => '2',  'b' => '1' ),
            array( 'a' => '2',  'b' => '2' )
        );
        $actual = $hour->describeAttributesValuesCombinations();
        $this->assertEquals( $combinations, $actual );
    }
    
    function testDescribeAttributesCombinationsSpecificEventType()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ), 'eventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ), 'eventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 1 ), 'eventB' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ), 'eventB' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        
        $combinations = array(
            array( 'a' => null, 'b' => null ),
            
            array( 'a' => '1',  'b' => null ),
            
            array( 'a' => null, 'b' => '1' ),
            array( 'a' => null, 'b' => '2' ),
            
            array( 'a' => 1,    'b' => '1' ),
            array( 'a' => 1,    'b' => '2' ),
            
        );
        $actual = $hour->describeAttributesValuesCombinations('eventA');
        $this->assertEquals( $combinations, $actual, 'should describe attribute value combinations for specific event type' );
    }
    
    function testDescribeEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testDescribeEventTypesCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->describeEventTypes(), 'returns array of distinct event types in use (compacted)' );
    }
    
    function testDescribeEventTypesUncompactedHitsDisabled() 
    {
		$this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $hour->describeEventTypes(), 'when uncompacted hits are disabled, describeEventTypes should return empty array' );
    }   
}