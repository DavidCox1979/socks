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
    

}