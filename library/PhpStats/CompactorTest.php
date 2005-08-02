<?php
class PhpStats_CompactorTest extends PhpStats_UnitTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const EVENTS_PER_HOUR = 5;
    
    function testCompact()
    {
        return $this->markTestIncomplete();
        
        //$this->insertDataHours( self::DAY, self::MONTH, self::YEAR );
//        $report = $this->getReport();
//        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
//        
//        $compactor = new PhpStats_Compactor( $report );
//        $compactor->compact();
//        
//        $this->db()->query('truncate table `event`');
//        
//        $report = $this->getReport();
//        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
    }    
    
    protected function getReport()
    {
        return new PhpStats_Report( array(
            'hour' => 1,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
    }

    protected function insertDataHours( $day, $month, $year )
    {
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $this->logHour( $hour, $day, $month, $year );
        }
    }
    
    protected function logHour( $hour, $day, $month, $year )
    {
        for( $repeat = 1; $repeat <= self::EVENTS_PER_HOUR; $repeat++ )
        {
            $time = mktime( $hour, $this->minute(), $this->second(), $day, $month, $year );
            $logger = new Phpstats_Logger();
            $logger->log( 'click', array(), $time );
        }
    }
    
    protected function minute()
    {
        return rand(1,59);
    }
    
    protected function second()
    {
        return rand(1,59);
    }

}