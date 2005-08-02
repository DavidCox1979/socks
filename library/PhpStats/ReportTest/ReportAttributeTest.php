<?php
class PhpStatas_ReportTest_ReportAttributeTest extends PhpStats_ReportTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const EVENTS_PER_HOUR = 3;
    
    function test1()
    {
        $this->insertDataHours( self::DAY, self::MONTH, self::YEAR );
        $timeParts = array(
            'hour' => 1,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $attributes = array( 'a' => 1 );
        $report = new PhpStats_Report( $timeParts, $attributes );
        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
    }
    
    function test2()
    {
        $this->insertDataHours( self::DAY, self::MONTH, self::YEAR );
        $timeParts = array(
            'hour' => 1,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $attributes = array( 'a' => 2 );
        $report = new PhpStats_Report( $timeParts, $attributes );
        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
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
            $logger->log( 'click', array( 'a' => 1 ), $time );
            $logger->log( 'click', array( 'a' => 2 ), $time );
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