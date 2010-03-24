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
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( array( 'a'=>1, 'b'=>null ), $hour->getAttributes() );
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
