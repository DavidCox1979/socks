<?php
class PhpStats_CompactorEarliestNonCompactedTest extends PhpStats_UnitTestCase
{
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
}