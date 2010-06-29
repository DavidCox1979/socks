<?php
class PhpStats_CompactorTest_CompactsTest extends PhpStats_UnitTestCase
{
    function testCompactsHoursInRange()
    {    
    	$timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertFalse( $hour->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertTrue( $hour->hasBeenCompacted() );
    } 
    
    function testCompactsDaysInRange()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertFalse( $day->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted(), 'should compact days between start & end time points' );
    }
    
    function testCompactsMonthsInRange()
    {        
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $month = new PhpStats_TimeInterval_Month( $timeParts );
        $this->assertFalse( $month->hasBeenCompacted() );
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $month = new PhpStats_TimeInterval_Month( $timeParts );
        $this->assertTrue( $month->hasBeenCompacted(), 'should compact months between start & end time points' );
    }
    
    function testCompactsDaysInRangeIndependantOfHours()
    {
        $timeParts = array( 'hour' => 1, 'day' => 1, 'month' => 1,'year' => 2002 );
        $this->logHour( $timeParts ); 
        
        $day = new PhpStats_TimeInterval_Day( $timeParts, array(), false );
        $day->compactChildren();
        
        $compactor = new PhpStats_Compactor();
        $compactor->compact();
        
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertTrue( $day->hasBeenCompacted(), 'should compact days between start & end time points, even if all of those hours have been compacted already' );
    }
    
    function testRequiresXAmountOfMemoryLimit()
    {
    	$mb = 1024*1024;
    	$actual = ini_get('memory_limit');
		return $this->assertTrue( 256 * $mb > $actual || $actual <= 0, 'should have a minimum memory_limit granted to it in php.ini (256MB)');
    }
    
	function testShouldNotRevisitPreviouslyCompactedHour()
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
        $compactor->compact();
        $compactor->compact();
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
	}
	
	function testShouldNotRevisitPreviouslyCompactedDay()
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
        $compactor->compact();
        $compactor->compact();
        
        $this->assertFalse( $compactor->earliestNonCompactedHour() );
        $this->assertFalse( $compactor->earliestNonCompactedDay() );
	}
    
    /**
    * Converts human readable file size (e.g. 10 MB, 200.20 GB) into bytes.
    *
    * @param string $str
    * @return int the result is in bytes
    * @author Svetoslav Marinov
    * @author http://slavi.biz
    */
    private function filesize2bytes($str)
    {
        $bytes = 0;

        $bytes_array = array(
            'B' => 1,
            'KB' => 1024,
            'MB' => 1024 * 1024,
            'GB' => 1024 * 1024 * 1024,
            'TB' => 1024 * 1024 * 1024 * 1024,
            'PB' => 1024 * 1024 * 1024 * 1024 * 1024,
        );

        $bytes = floatval($str);

        if (preg_match('#([KMGTP]?B)$#si', $str, $matches) && !empty($bytes_array[$matches[1]]))
        {
            $bytes *= $bytes_array[$matches[1]];
        }

        return intval(round($bytes, 2));
    }

}