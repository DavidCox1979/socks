<?php
class PhpStats_Report_HourlyTest extends PhpStats_ReportTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const EVENTS_PER_HOUR = 5;
    
    function testReportHours()
    {
        $this->insertDataHours( self::DAY, self::MONTH, self::YEAR );
        $report = new PhpStats_Report_Hourly( array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
        $intervals = $report->getHours('click');
        $this->assertEquals( self::EVENTS_PER_HOUR, $intervals[1]->getCount('clicks'), 'month 1\'s clicks should equal 5' );
    }
    
    protected function insertDataHours( $day, $month, $year )
    {
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $this->logHour( $hour, $day, $month, $year );
        }
        // should not count this
        $time = mktime( $hour, $this->minute(), $this->second(), $day, $month, $year-1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
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