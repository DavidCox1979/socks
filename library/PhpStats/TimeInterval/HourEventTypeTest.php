<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourEventTypeTest extends PhpStats_TimeInterval_HourTestCase
{
    function testDescribeUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testDescribeCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->describeEventTypes(), 'returns array of distinct event types in use (compacted)' );
    }
    
    function testDescribeUncompactedHitsDisabled() 
    {
		$this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $this->logHour( $this->getTimeParts(), array(), 'eventB' );
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $hour->describeEventTypes(), 'when uncompacted hits are disabled, describeEventTypes should return empty array' );
    }
    
    function testWhenEventTypeDoNotMatch_ShouldNotCount()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 0, $hour->getCompactedCount('click'), 'when event types do not match, should not count' );
    }
    
    function testWhenEventTypeMatch_ShouldCount()
    {
        $this->logHour( $this->getTimeParts(), array(), 'foo' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 1, $hour->getCompactedCount('foo'), 'when event types match, should count' );
    }   
}
