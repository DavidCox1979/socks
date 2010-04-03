<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayInFutureTest extends PhpStats_TimeInterval_HourTestCase
{
	function testWhenInPresent_ShouldReturnFalse()
    {
		$day = new PhpStats_TimeInterval_Day( array( 'day'=>1, 'month'=>1, 'year'=>2002 ) );
		$now = new Zend_Date();
		$now->setHour(1);
		$now->setDay(1);
		$now->setMonth(1);
		$now->setYear(2002);
		$this->assertFalse( $day->isInFuture( $now ) );
    }
    
    function testWhenInPastDay_ShouldReturnFalse()
    {
		$day = new PhpStats_TimeInterval_Day( array( 'day'=>1, 'month'=>1, 'year'=>2002 ) );
		$now = new Zend_Date();
		$now->setDay(2);
		$now->setMonth(1);
		$now->setYear(2002);
		$this->assertFalse( $day->isInFuture( $now ) );
    }
    
    function testWhenYearInFuture_ShouldReturnTrue_EventIfMonthInPast()
    {
		$dayObj = new PhpStats_TimeInterval_Day( array( 'year'=>2037, 'month'=>1, 'day'=>2) );
		
		$year = 2002;
		$month = 2;
		$day = 1;
		$now = new Zend_Date(mktime(0,0,0,$month,$day,$year));
		
		$this->assertTrue( $dayObj->isInFuture( $now ) );
    }
   
    function testWhenYearInFuture_ShouldReturnTrue_EventIfDayInPast()
    {
		$dayObj = new PhpStats_TimeInterval_Day( array( 'year'=>2037, 'month'=>1, 'day'=>1 ) );
		
		$year = 2002;
		$month = 2;
		$day = 2;
		$now = new Zend_Date(mktime(0,0,0,$month,$day,$year));
		
		$this->assertTrue( $dayObj->isInFuture( $now ) );
    }
   
}