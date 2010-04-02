<?php
class PhpStats_CompactorTest extends PhpStats_UnitTestCase
{
    function testNoLastCompacted()
    {
        $compactor = new PhpStats_Compactor;
        $this->assertFalse( $compactor->lastCompacted() );
    }
    
    function testLastCompacted()
    {
        $timeParts = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        
        $this->logHour( $timeParts );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $timeParts, $compactor->lastCompacted() );
    }
    
    function testLastCompacted2()
    {
        $timeParts = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $timeParts );
        
        $timeParts = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $timeParts );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $timeParts, $compactor->lastCompacted() );
    }
    
    function testLastCompactedDay()
    {
        $this->logHour( array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        $this->logHour( array('hour' => 2,'day' => 2,'month' => 1,'year' => 2002) );
        $day = new PhpStats_TimeInterval_Day( array('hour' => 2,'day' => 2,'month' => 1,'year' => 2002) );
        $day->compact();
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array('day'=>2,'month'=>1,'year'=>2002), $compactor->lastCompactedDay(), 'last compacted day should return the last day that has been compacted' );
    }
    
    function testEnumerateHourIntervalsWithinSingleDay()
    {
        $start = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $end = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 3, count( $hours ));
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[2]->getTimeParts() );
    }
    
    function testEnumerateHourIntervalsOverMultipleDays()
    {
        $start = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $end = array('hour' => 3,'day' => 2,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 49, count( $hours ));
        $this->assertEquals( array( 'hour' => 0, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts(), 'starts at midnight of the starting day');
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[2]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 23, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[23]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[24]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 1, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[25]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[26]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[27]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[48]->getTimeParts(), 'goes beyond end time to midnight of the last day' );
    }
    
    function testEnumerateHourIntervalsOverMultipleMonths()
    {
        $start = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $end = array('hour' => 1,'day' => 4,'month' => 3,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 3002, count( $hours ));
        $this->assertEquals( array( 'hour' => 0, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 31, 'month' => 1, 'year' => 2002 ), $hours[744]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 23, 'day' => 31, 'month' => 3, 'year' => 2002 ), $hours[2904]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 4, 'month' => 3, 'year' => 2002 ), $hours[3001]->getTimeParts() );
    }
    
    function testEnumerateHourIntervalsOverMultipleYears()
    {
        return $this->markTestIncomplete();
    }
    
    function testEnumerateDayIntervalsWithinSingleMonth()
    {
        $start = array('day' => 1,'month' => 1,'year' => 2002);
        $end = array('day' => 3,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 3, count( $days ));
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $days[1]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 1, 'year' => 2002 ), $days[2]->getTimeParts() );
    }
    
    function testEnumerateDayIntervalsSpanningMultipleMonths()
    {
        $start = array('day' => 1,'month' => 1,'year' => 2002);
        $end = array( 'day' => 3, 'month' => 3,'year' => 2002 );
        
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 62, count( $days ));
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $days[1]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 1, 'year' => 2002 ), $days[2]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 2, 'year' => 2002 ), $days[33]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 3, 'year' => 2002 ), $days[61]->getTimeParts() );
    }
    
    function testEnumerateDayIntervalspanningMultipleYears()
    {
        return $this->markTestIncomplete();
    }
    
    function testEnumerateMonthWithinSingleYear()
    {
        return $this->markTestIncomplete();
    }
    
    function testEnumerateMonthSpanningMultipleYear()
    {
        return $this->markTestIncomplete();
    }
    
    function testCompactsHoursInRange()
    {    
    	$timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertFalse( $hour->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertTrue( $hour->hasBeenCompacted() );
    } 
    
    function testCompactsDaysInRange()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertFalse( $day->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted(), 'should compact days between start & end time points' );
    }
    
    function testCompactsDaysInRangeIndependantOfHours()
    {
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $day = new PhpStats_TimeInterval_Day( $timeParts, array(), false );
        $day->compactChildren();
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted(), 'should compact days between start & end time points, even if all of those hours have been compacted already' );
    }
    
    function testAcquiresLock()
    {
		$compactor = new PhpStats_Compactor();
		$this->assertFalse( $compactor->hasLock() );
		$compactor->acquireLock();
		$this->assertTrue( $compactor->hasLock() );
    }
    
    function testAcquiresLockAndBlocksConcurrentCompacters()
    {
		$compactor1 = new PhpStats_Compactor();
		$compactor2 = new PhpStats_Compactor();
		$compactor1->acquireLock();
		$this->assertFalse( $compactor2->hasLock() );
    }
    
    function testFreesLock()
    {
		$compactor = new PhpStats_Compactor();
		$compactor->acquireLock();
		$compactor->freeLock();
		$this->assertFalse( $compactor->hasLock() );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenCantAcquireLockThrowsException()
    {
		$compactor1 = new PhpStats_Compactor();
		$compactor2 = new PhpStats_Compactor();
		$compactor1->acquireLock();
		$compactor2->acquireLock();
    }
    
    function testCompactingWithoutLockShouldAcquireLock()
    {
		return $this->markTestIncomplete();
    }
    
    function testRequiresXAmountOfMemoryLimit()
    {
    	$mb = 1024*1024;
    	$actual = ini_get('memory_limit');
		return $this->assertTrue( 256 * $mb > $actual || $actual <= 0, 'should have a minimum memory_limit granted to it in php.ini (256MB)');
    }
    
	/**
	* Converts human readable file size (e.g. 10 MB, 200.20 GB) into bytes.
	*
	* @param string $str
	* @return int the result is in bytes
	* @author Svetoslav Marinov
	* @author http://slavi.biz
	*/
	private function filesize2bytes($str)
	{
	    $bytes = 0;

	    $bytes_array = array(
	        'B' => 1,
	        'KB' => 1024,
	        'MB' => 1024 * 1024,
	        'GB' => 1024 * 1024 * 1024,
	        'TB' => 1024 * 1024 * 1024 * 1024,
	        'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
	    );

	    $bytes = floatval($str);

	    if (preg_match('#([KMGTP]?B)$#si', $str, $matches) && !empty($bytes_array[$matches[1]])) {
	        $bytes *= $bytes_array[$matches[1]];
	    }

	    $bytes = intval(round($bytes, 2));

	    return $bytes;
	} 
	    
	function testShouldNotRevisitPreviouslyCompactedHour()
	{
		$timeParts = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $timeParts );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $threeOClock = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $threeOClock );
        
        $twoOClock = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $twoOClock );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        $compactor->compact();
        
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
        
        
	}
	
	function testShouldNotRevisitPreviouslyCompactedDay()
	{
		$timeParts = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $timeParts );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $threeOClock = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $threeOClock );
        
        $twoOClock = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $twoOClock );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        $compactor->compact();
        
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
        $this->assertFalse( $compactor->earliestNonCompactedDay() );
        
        
	}
	
	function testNoUncompactedDay()
	{
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        
        $this->assertFalse( $compactor->earliestNonCompactedDay() );
	}
	
	function testNoUncompactedHour()
	{
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
	}
	
	function testLastDayOfMonthCompactedIncrementsMonth()
	{
		$this->logHour( array( 'hour' => 1,'day' => 31,'month' => 1,'year' => 2002) );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 2,'year' => 2002) );
        
        $this->assertEquals( array( 'day' => 1, 'month' => 2, 'year' => 2002 ), $compactor->earliestNonCompactedDay() );
	}
	
	function testExcludesCurrentHour()
	{
		$time = new Zend_Date();
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        
        $this->logHour( array( 'hour' => $hour,'day' => $day,'month' => $month,'year' => $year ) );
        $compactor = new PhpStats_Compactor;
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
	}
	
	function testExcludesCurrentDay()
	{
		$time = new Zend_Date();
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        
        $this->logHour( array( 'hour' => $hour,'day' => $day,'month' => $month,'year' => $year ) );
        $compactor = new PhpStats_Compactor;
        
        $this->assertFalse( $compactor->earliestNonCompactedDay() );
	}

}