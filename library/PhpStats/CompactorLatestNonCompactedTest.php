<?php
class PhpStats_CompactorLatestNonCompactedTest extends PhpStats_UnitTestCase
{
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
}