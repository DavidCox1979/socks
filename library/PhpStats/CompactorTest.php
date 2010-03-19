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
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $timeParts );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $timeParts, $compactor->lastCompacted() );
    }
    
    function testLastCompacted2()
    {
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $timeParts );
        
        $timeParts = array(
            'hour' => 2,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $timeParts );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $timeParts, $compactor->lastCompacted() );
    }
    
    function testEarliestNonCompacted()
    {
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $timeParts );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $threeOClock = array(
            'hour' => 3,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $threeOClock );
        
        $twoOClock = array(
            'hour' => 2,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $twoOClock );
        
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $twoOClock, $compactor->earliestNonCompacted() );
    }
    
    function testEarliestNonCompacted2()
    {
        $oneOClock = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $oneOClock );
        
        
        $threeOClock = array(
            'hour' => 3,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $threeOClock );
        
        $twoOClock = array(
            'hour' => 2,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $twoOClock );
        
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $oneOClock, $compactor->earliestNonCompacted() );
    }
    
    function testLatestNonCompacted()
    {
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $timeParts );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $twoOClock = array(
            'hour' => 2,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $twoOClock );
        
        $threeOClock = array(
            'hour' => 3,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        
        $this->logHour( $threeOClock );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( $threeOClock, $compactor->latestNonCompacted() );
    }
    
    function testEnumerateHourIntervalsWithinSingleDay()
    {
        $start = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $end = array(
            'hour' => 3,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $compactor = new PhpStats_Compactor();
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 3, count( $hours ));
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[2]->getTimeParts() );
    }
    
    function testEnumerateHourIntervalsOverMultipleDays()
    {
        $start = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $end = array(
            'hour' => 3,
            'day' => 2,
            'month' => 1,
            'year' => 2002
        );
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
        $start = array(
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $end = array(
            'day' => 3,
            'month' => 1,
            'year' => 2002
        );
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 3, count( $days ));
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $days[1]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 1, 'year' => 2002 ), $days[2]->getTimeParts() );
    }
    
    function testEnumerateDayIntervalsSpanningMultipleMonths()
    {
        $start = array(
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $end = array(
            'day' => 3,
            'month' => 3,
            'year' => 2002
        );
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
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $this->logHour( $timeParts ); 
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertFalse( $hour->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact( $compactor->earliestNonCompacted(), $compactor->latestNonCompacted() );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertTrue( $hour->hasBeenCompacted() );
    } 
    
    function testCompactsDaysInRange()
    {        
        $timeParts = array(
            'hour' => 1,
            'day' => 1,
            'month' => 1,
            'year' => 2002
        );
        $this->logHour( $timeParts ); 
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertFalse( $day->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact( $compactor->earliestNonCompacted(), $compactor->latestNonCompacted() );
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted() );
    }  

}