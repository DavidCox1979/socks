<?php
class PhpStats_CompactorTest_LastCompactedTest extends PhpStats_UnitTestCase
{
    function testWhenNoLastCompacted()
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
    
    function testLastCompactedMonth()
    {
        $this->logHour( array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002) );
        $this->logHour( array('hour' => 2,'day' => 2,'month' => 2,'year' => 2002) );
        $month = new PhpStats_TimeInterval_Month( array('hour' => 2,'day' => 2,'month' => 2,'year' => 2002) );
        $month->compact();
        $compactor = new PhpStats_Compactor;
        $this->assertEquals( array('month'=>2,'year'=>2002), $compactor->lastCompactedMonth(), 'should return the last monththat has been compacted' );
    }