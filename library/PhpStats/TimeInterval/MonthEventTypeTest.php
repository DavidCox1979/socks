<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthEventTypeTest extends PhpStats_TimeInterval_TestCase
{
    function testDescribeEventTypesExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array(), 'EventA' );
        $this->logHour( $this->getTimeParts(), array(), 'EventB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array( 'EventA', 'EventB' ), $month->describeEventTypes(), 'returns array of distinct event types in use' );
    }
}