<?php
class PhpStats_CompactorTest_EarliestNonCompactedMonthTest extends PhpStats_UnitTestCase
{
    function testWhenAllMonthsCompactedShouldReturnFalse()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        
        $month = new PhpStats_TimeInterval_Month( array('month'=>1,'year'=>2002));
        $month->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertFalse( $compactor->earliestNonCompactedMonth(), 'if all intervals have been compacted; should return false' );
    }
    
    function testShouldNotConsiderMonthAsUncompactedUntilFollowingMonth()
    {
        $time = new Zend_Date();
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        
        $this->logHour( array( 'hour' => $hour,'day' => $day,'month' => $month,'year' => $year ) );
        $compactor = new PhpStats_Compactor;
        
        $this->assertFalse( $compactor->earliestNonCompactedMonth(), 'should never compact a month / consider a month uncompacted until midnight the following month' );
    }
    
    function testShouldFindEarliestMonth()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 3,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $compactor = new PhpStats_Compactor();
        $this->assertEquals( array( 'month' => 3,'year' => 2002 ), $compactor->earliestNonCompactedMonth(), 'should find earliest non compacted day' );
    }
    
    function testWhenMonthCompactedShouldReturnNextMonth()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002));
        $this->logHour( array('hour' => 1,'day' => 1,'month' => 2,'year' => 2002));
        
        $month = new PhpStats_TimeInterval_Month( array('month'=>1,'year'=>2002));
        $month->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'month' => 2, 'year' => 2002 ), $compactor->earliestNonCompactedMonth(), 'when a month is compacted; should return the next month' );
    }
    
    function testLastMonthOfYearCompactedIncrementsMonth()
    {
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 12,'year' => 2002) );
        $this->logHour( array( 'hour' => 1,'day' => 1,'month' => 1, 'year' => 2003) );
        
        $month = new PhpStats_TimeInterval_Month( array('month'=>12,'year'=>2002));
        $month->compact();
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'month' => 1, 'year' => 2003 ), $compactor->earliestNonCompactedMonth(), 'when the last month of the year has been compacted, and there are subsequent months, the next month should be returned' );
    }    
    
    function testConsidersYear()
    {
        $year1 = array( 'hour' => 1,'day' => 2,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $year2 = array('hour' => 2, 'day' => 1,'month' => 2,'year' => 2003);
        $this->logHour( $year2 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array( 'month' => 1,'year' => 2002), $compactor->earliestNonCompactedMonth(), 'should consider year' );
    }
    
    function testWhenDaysCompactedDayShouldStillBeUncompacted()
    {
        $day1 = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $day15 = array( 'hour' => 1, 'day' => 15, 'month' => 1,'year' => 2002 );
        
        $this->logHour( $day1 );
        $day = new PhpStats_TimeInterval_Day( $day1 );
        $day->compact();
        
        $compactor = new PhpStats_Compactor();
        $this->assertEquals( array( 'month'=>1,'year'=>2002), $compactor->earliestNonCompactedMonth(), 'when there are days that fall chronoligcally after an uncompacted month, should still mark the month as uncompacted' );
    }

}