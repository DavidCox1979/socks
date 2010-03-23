<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayDescribeEventTypeTest extends PhpStats_TimeInterval_DayTestCase
{
    function testDescribeEventTypes()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $day->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testCompacted()
    {
        $this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'eventA', 'eventB' ), $day->describeEventTypes(), 'returns array of distinct event types in use (compacted)' );
    }
    
    function testUncompactedHitsDisabled() 
    {
    	$this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->clearUncompactedEvents();
        $this->assertEquals( array(), $day->describeEventTypes(), 'when uncompacted hits are disabled, and day is not compacted, describeEventTypes should return empty array.' );
    }
    
    function testUncompactedHitsDisabled2() 
    {
    	$this->logThisDayWithHour( 1, array(), 'eventA' );
        $this->logThisDayWithHour( 1, array(), 'eventB' );
        
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array() );
        $hour->compact();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $day->describeEventTypes(), 'when uncompacted hits are disabled, and day is not compacted, describeEventTypes should return empty array (even if an hour is compacted).' );
    }
    
    function testExcludesDifferentDays()
    {
        $this->logHour( array('hour'=>1, 'day'=>1, 'month'=>1, 'year'=>2002), array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( array(
            'day' => 2,
            'month' => 1,
            'year' => 2002
        ));
        $this->assertEquals( array(), $day->describeEventTypes(), 'excludes different time interavals from describeEventTypes()' );
    }
    
    function testExcludesDifferentMonths()
    {
		return $this->markTestIncomplete(); 
    }
    
    function testExcludesDifferentYears()
    {
		return $this->markTestIncomplete(); 
    }
	
}