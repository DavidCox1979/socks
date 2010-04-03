<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourCompactUniquesTest extends PhpStats_TimeInterval_HourTestCase
{
    function testWhenIPsDiffer_ShouldIncrementUniqueCount()
    {
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.1' );
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.2' );
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.3' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( 3, $hour->getCount( 'click', array(), true ), 'when IPs differ, should increment unique count by the # of IPs' );
    }
    
    function testWhenIPsDiffer_ShouldCountNonUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 4, $hour->getCount( 'click', array(), false ), 'when IPs differ should count non-uniques' );
    }
}