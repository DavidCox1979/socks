<?php
class PhpStats_CompactorTest_EarliestNonCompactedHourTest extends PhpStats_UnitTestCase
{
    function testWhenNoneCompactedShouldReturnFirstInterval()
    {
        $oneOClock = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $oneOClock );
        
        $threeOClock = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $threeOClock );
        
        $twoOClock = array('hour' => 2,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $twoOClock );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $oneOClock, $compactor->earliestNonCompactedHour(), 'when no intervals compacted, should return first interval' );
    }
    
    function testWhenIntervalCompactedShouldReturnSubsequentInterval()
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
        $this->assertEquals( $twoOClock, $compactor->earliestNonCompactedHour(), 'if an interval has been compacted, should return the subsequent interval' );
    }
    
    function testConsidersDay()
    {
        $day1 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $day1 );
        
        $day2 = array('hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $day2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'hour' =>1, 'day' => 1,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedHour() );
    }
    
    function testConsidersMonth()
    {
        $month1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $month2 = array('hour' => 3,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedHour() );
    }
    
    function testConsidersYear()
    {
        $year1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $year2 = array('hour' => 2, 'day' => 1,'month' => 2,'year' => 2003);
        $this->logHour( $year2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedHour() );
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
    
    function testNoUncompactedHour()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        
        $compactor = new PhpStats_Compactor;
        $compactor->compact();
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
    }
}