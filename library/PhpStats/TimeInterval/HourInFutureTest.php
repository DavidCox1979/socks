<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourInFutureTest extends PhpStats_TimeInterval_HourTestCase
{
	function testWhenInPresent_ShouldReturnFalse()
    {
		$hour = new PhpStats_TimeInterval_Hour( array( 'hour'=>1, 'day'=>1, 'month'=>1, 'year'=>2002 ) );
		$now = new Zend_Date();
		$now->setHour(1);
		$now->setDay(1);
		$now->setMonth(1);
		$now->setYear(2002);
		$this->assertFalse( $hour->isInFuture( $now ) );
    }
    
    function testWhenInPastDay_ShouldReturnFalse()
    {
		$hour = new PhpStats_TimeInterval_Hour( array( 'hour'=>1, 'day'=>1, 'month'=>1, 'year'=>2002 ) );
		$now = new Zend_Date();
		$now->setHour(1);
		$now->setDay(2);
		$now->setMonth(1);
		$now->setYear(2002);
		$this->assertFalse( $hour->isInFuture( $now ) );
    }
    
    function testWhenInPastMonth_ShouldReturnFalse()
    {
		$hourObj = new PhpStats_TimeInterval_Hour( array( 'year'=>2037, 'month'=>1, 'day'=>2, 'hour'=>3 ) );
		
		$year = 2037;
		$month = 2;
		$day = 1;
		$hour = 1;
		$now = new Zend_Date(mktime($hour,0,0,$month,$day,$year));
		
		$this->assertFalse( $hourObj->isInFuture( $now ) );
    }
      
    function testWhenYearInFuture_ShouldReturnTrue_EventIfMonthInPast()
    {
		$hourObj = new PhpStats_TimeInterval_Hour( array( 'year'=>2037, 'month'=>1, 'day'=>2, 'hour'=>3 ) );
		
		$year = 2002;
		$month = 2;
		$day = 1;
		$hour = 1;
		$now = new Zend_Date(mktime($hour,0,0,$month,$day,$year));
		
		$this->assertTrue( $hourObj->isInFuture( $now ) );
    }

    function testWhenYearInFuture_ShouldReturnTrue_EventIfDayInPast()
    {
		$hourObj = new PhpStats_TimeInterval_Hour( array( 'year'=>2037, 'month'=>1, 'day'=>1, 'hour'=>3 ) );
		
		$year = 2002;
		$month = 1;
		$day = 2;
		$hour = 1;
		$now = new Zend_Date(mktime($hour,0,0,$month,$day,$year));
		
		$this->assertTrue( $hourObj->isInFuture( $now ) );
    }

    function testWhenYearInFuture_ShouldReturnTrue_EventIfHourInPast()
    {
		$hourObj = new PhpStats_TimeInterval_Hour( array( 'year'=>2037, 'month'=>1, 'day'=>1, 'hour'=>1 ) );
		
		$year = 2002;
		$month = 1;
		$day = 2;
		$hour = 3;
		$now = new Zend_Date(mktime($hour,0,0,$month,$day,$year));
		
		$this->assertTrue( $hourObj->isInFuture( $now ) );
    }
}