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
    
    function testEarliestNonCompacted()
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
        $this->assertEquals( $twoOClock, $compactor->earliestNonCompactedHour() );
    }
    
    function testEarliestNonCompactedHourConsidersMonth()
    {
    	$month1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $month2 = array('hour' => 3,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedHour() );
    }
    
    function testEarliestNonCompactedHourConsidersYear()
    {
    	$year1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $year2 = array('hour' => 2, 'day' => 1,'month' => 2,'year' => 2003);
        $this->logHour( $year2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedHour() );
    }
    
    function testEarliestNonCompactedDayConsidersMonth()
    {
    	$month1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $month2 = array('hour' => 3,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 2,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedDay() );
    }
    
    function testEarliestNonCompactedDayConsidersYear()
    {
    	$year1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $year2 = array('hour' => 2, 'day' => 1,'month' => 2,'year' => 2003);
        $this->logHour( $year2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 2,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedDay() );
    }
    
    function testEarliestNonCompacted2()
    {
        $oneOClock = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $oneOClock );
        
        $threeOClock = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $threeOClock );
        
        $twoOClock = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $twoOClock );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $oneOClock, $compactor->earliestNonCompactedHour() );
    }
    
    function testEarliestNonCompactedDay()
    {
    	$day1 = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
    	$day15 = array( 'hour' => 1, 'day' => 15, 'month' => 1,'year' => 2002 );
    	
    	$this->logHour( $day1 );
    	$hour = new PhpStats_TimeInterval_Hour( $day1 );
    	$hour->compact();
    	$hour = new PhpStats_TimeInterval_Hour( $day15 );
    	
    	$compactor = new PhpStats_Compactor();

    	$this->assertEquals( array( 'day'=>1,'month'=>1,'year'=>2002), $compactor->earliestNonCompactedDay(), 'earliest non compacted day should not depend on the hours being compacted or not' );
		// if it has compacted the HOURS from the 12th to the 15th, but not any of the days, it should still count the 12th DAY as uncompacted
    }
    
    function testEarliestNonCompactedDay2()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 3, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $hour = new PhpStats_TimeInterval_Hour( array( 'hour' => 1, 'day' => 2, 'month' => 1,'year' => 2002 ) );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor();
        $this->assertEquals( array( 'day' => 3, 'month' => 1,'year' => 2002 ), $compactor->earliestNonCompactedDay(), 'should find earliest non compacted day' );
    }
    
    function testLatestNonCompactedHour()
    {
        $oneOClock = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $oneOClock );
        
        $hour = new PhpStats_TimeInterval_Hour( $oneOClock );
        $hour->compact();
        
        $twoOClock = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $twoOClock );
        
        $threeOClock = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $threeOClock );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $threeOClock, $compactor->latestnonCompactedHour() );
    }
    
    function testLatestNonCompactedHourByDay()
    {
        $day1 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $day1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $day1 );
        $hour->compact();
        
        $day2 = array('hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $day2 );
        
        $day3 = array('hour' => 1,'day' => 3,'month' => 1,'year' => 2002);
        $this->logHour( $day3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $day3, $compactor->latestnonCompactedHour(), 'should find last non compacted hour by day' );
    }
    
    function testLatestNonCompactedDay()
    {
        $day1 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $day1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $day1 );
        $hour->compact();
        
        $day2 = array('hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $day2 );
        
        $day3 = array('hour' => 1,'day' => 3,'month' => 1,'year' => 2002);
        $this->logHour( $day3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array('day' => 3,'month' => 1,'year' => 2002), $compactor->latestnonCompactedDay(), 'should find last non compacted day' );
    }
    
    function testLatestNonCompactedMonth()
    {
        $month1 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $month1 );
        $hour->compact();
        
        $month2 = array('hour' => 1,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $month3 = array('hour' => 1,'day' => 1,'month' => 3,'year' => 2002);
        $this->logHour( $month3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $month3, $compactor->latestnonCompactedHour(), 'should find last non compacted (month)' );
    }
   
    function testLatestNonCompactedYear()
    {
    	$year1 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $year1 );
        $hour->compact();
        
        $year2 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2003);
        $this->logHour( $year2 );
        
        $year3 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2004);
        $this->logHour( $year3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $year3, $compactor->latestnonCompactedHour(), 'should find last non compacted (year)' );
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

        $this->assertEquals( 27, count( $hours ));
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 23, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[22]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[23]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 1, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[24]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[25]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[26]->getTimeParts() );
    }
    
    function testEnumerateHourIntervalsOverMultipleMonths()
    {
        return $this->markTestIncomplete();
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
    
    // ex if there is traffic for days 12-15, we should go for 1-15 (to make sure that all days in the month
    // were compacted) ( can't prune the event table before the month is up.. I think.)
    function testEarliestNonCompactedGoesToBeginningOfMonth()
    {
    	$this->logHour( array('hour' => 1, 'day' => 12,'month' => 1,'year' => 2002));
    	$this->logHour( array('hour' => 1,'day' => 15,'month' => 1,'year' => 2002));
    	$compactor = new PhpStats_Compactor();
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $compactor->earliestNonCompactedHour(), 'earliest non compacted goes to beginning of month' );
    }
    
    function testEarliestNonCompactedNoTraffic()
    {
    	$compactor = new PhpStats_Compactor();
        $this->assertFalse( $compactor->earliestNonCompactedHour(), 'earliest non compacted should return false if there is nothing to compact (no traffic at all)' );
    }
    
    function testEarliestNonCompactedAllTrafficCompacted()
    {
    	$this->logHour( array('hour' => 1, 'day' => 12,'month' => 1,'year' => 2002) );
    	$hour = new PhpStats_TimeInterval_Hour( array('hour' => 1, 'day' => 12,'month' => 1,'year' => 2002) );
    	$hour->compact();
    	$compactor = new PhpStats_Compactor();
        $this->assertFalse( $compactor->earliestNonCompactedHour(), 'earliest non compacted should return false if there is nothing to compact (all traffic compacted)' );
    }
    
    function testEarliestNonCompactedDayAfterLastCompacted()
    {
    	$this->logHour( array( 'hour' => 1,'day' => 12,'month' => 1,'year' => 2002));
    	$this->logHour( array('hour' => 1,'day' => 15,'month' => 1,'year' => 2002));
    	$compactor = new PhpStats_Compactor();
        $days = $compactor->compact();
        $this->logHour( array('hour' => 1,'day' => 16,'month' => 1,'year' => 2002));
        $this->assertEquals( array( 'day' => 16, 'month' => 1, 'year' => 2002 ), $compactor->earliestNonCompactedDay() );
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
    
    function testAcquiresLockAndBlocksConcurrentCompacters()
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
