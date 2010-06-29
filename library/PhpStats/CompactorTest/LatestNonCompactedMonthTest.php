<?php
class PhpStats_CompactorTest_LatestNonCompactedMonthTest extends PhpStats_UnitTestCase
{
	function testLatestNonCompactedMonth()
    {
        $month1 = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $month1 );
        
        $month = new PhpStats_TimeInterval_Month( $month1 );
        $month->compact();
        
        $month2 = array('hour' => 1,'day' => 1,'month' => 2,'year' => 2002);
        $this->logHour( $month2 );
        
        $month3 = array('hour' => 1,'day' => 1,'month' => 3,'year' => 2002);
        $this->logHour( $month3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array('month' => 3,'year' => 2002), $compactor->latestnonCompactedMonth(), 'should find last non compacted day' );
    }

   
    function testConsidersYear()
    {
        $year1 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $this->logHour( $year1 );
        
        $month = new PhpStats_TimeInterval_Month( $year1 );
        $month->compact();
        
        $year2 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2003);
        $this->logHour( $year2 );
        
        $year3 = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2004);
        $this->logHour( $year3 );
        
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array('month' => 1,'year' => 2004), $compactor->latestnonCompactedMonth(), 'should find last non compacted (year)' );
    }
}