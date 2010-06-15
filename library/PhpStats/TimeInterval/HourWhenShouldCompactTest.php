<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourWhenShouldCompactTest extends PhpStats_TimeInterval_HourTestCase
{
	function testWhenInPast_ShouldCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInPast' )
        	->will( $this->returnValue(true) );
        
        $this->assertTrue( $hour->canCompact(), 'when in past, should compact' );
    }
    
    function testWhenInPresent_ShouldNotCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInPresent' )
        	->will( $this->returnValue(true) );

        $this->assertFalse( $hour->canCompact(), 'when is in present, should not be able to compact' );
    }
    
    function testWhenInFuture_ShouldNotCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInFuture' )
        	->will( $this->returnValue(true) );
        
        $this->assertFalse( $hour->canCompact(), 'when is in future, should not be able to compact' );
    }
}