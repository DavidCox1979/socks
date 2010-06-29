<?php
class PhpStats_CompactorTest_LatestNonCompactedHourTest extends PhpStats_UnitTestCase
{
	
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
    
    function testConsidersDay()
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
    
    function testConsidersMonth()
    {
        $day1 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $day1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $day1 );
        $hour->compact();
        
        $day2 = array('hour' => 1,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $day2 );
        
        $day3 = array('hour' => 1,'day' => 1,'month' => 3,'year' => 2002);
        $this->logHour( $day3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $day3, $compactor->latestnonCompactedHour(), 'should find last non compacted hour by day' );
    }
    
    function testConsidersYear()
    {
        $day1 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $day1 );
        
        $hour = new PhpStats_TimeInterval_Hour( $day1 );
        $hour->compact();
        
        $day2 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2003);
        $this->logHour( $day2 );
        
        $day3 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2004);
        $this->logHour( $day3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $day3, $compactor->latestnonCompactedHour(), 'should find last non compacted hour by day' );
    }   
}