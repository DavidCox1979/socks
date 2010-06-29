<?php
class PhpStats_CompactorTest_EarliestNonCompactedDayTest extends PhpStats_UnitTestCase
{
    function testWhenAllDaysCompactedShouldReturnFalse()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        
        $day = new PhpStats_TimeInterval_Day( array('day'=>1,'month'=>1,'year'=>2002));
        $day->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertFalse( $compactor->earliestNonCompactedDay(), 'if all intervals have been compacted; should return false' );
    }
    
    function testShouldNotConsiderDayAsUncompactedUntilNextMorning()
    {
        $time = new Zend_Date();
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        
        $this->logHour( array( 'hour' => $hour,'day' => $day,'month' => $month,'year' => $year ) );
        $compactor = new PhpStats_Compactor;
        
        $this->assertFalse( $compactor->earliestNonCompactedDay(), 'should never compact a day / consider a day uncompacted until midnight the following morning' );
    }
    
    function testShouldFindEarliestDay()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 3, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $compactor = new PhpStats_Compactor();
        $this->assertEquals( array( 'day' => 3, 'month' => 1,'year' => 2002 ), $compactor->earliestNonCompactedDay(), 'should find earliest non compacted day' );
    }
    
    function testWhenDayCompactedShouldReturnNextDay()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002));
        $this->logHour( array('hour' => 1,'day' => 2,'month' => 1,'year' => 2002));
        
        $day = new PhpStats_TimeInterval_Day( array('day'=>1,'month'=>1,'year'=>2002));
        $day->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $compactor->earliestNonCompactedDay(), 'when a day is compacted; should return the next day' );
    }
    
    function testLastDayOfMonthCompactedIncrementsMonth()
    {
        $this->logHour( array( 'hour' => 1,'day' => 31,'month' => 1,'year' => 2002) );
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 2,'year' => 2002) );
        
        $day = new PhpStats_TimeInterval_Day( array('day'=>31,'month'=>1,'year'=>2002));
        $day->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 1, 'month' => 2, 'year' => 2002 ), $compactor->earliestNonCompactedDay(), 'when the last day of the month has been compacted, and there are subsequent months, the next month should be returned' );
    }
    
    function testConsidersMonth()
    {
        $month1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $month2 = array('hour' => 3,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 2,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedDay(), 'should consider month' );
    }
    
    function testConsidersYear()
    {
        $year1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $year2 = array('hour' => 2, 'day' => 1,'month' => 2,'year' => 2003);
        $this->logHour( $year2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'day' => 2,'month' => 1,'year' => 2002), $compactor->earliestNonCompactedDay(), 'should consider year' );
    }
    
    function testWhenHoursCompactedDayShouldStillBeUncompacted()
    {
        $day1 = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $day15 = array( 'hour' => 1, 'day' => 15, 'month' => 1,'year' => 2002 );
        
        $this->logHour( $day1 );
        $hour = new PhpStats_TimeInterval_Hour( $day1 );
        $hour->compact();
        
        $compactor = new PhpStats_Compactor();

        $this->assertEquals( array( 'day'=>1,'month'=>1,'year'=>2002), $compactor->earliestNonCompactedDay(), 'when there are hours that fall chronoligcally after an uncompacted day, should still mark the day as uncompacted' );
    }
    
}