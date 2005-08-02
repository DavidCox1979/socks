<?php
class PhpStats_Report_DayTest extends PhpStats_ReportTestCase
{
    const HOUR = 1;
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 5;
    
    function testReportHours()
    {
        // should count this
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        
        // should not count this
        $time = mktime( 1, $this->minute(), $this->second(), 5, 4, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
        
        $report = new PhpStats_Report_Day( array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
        
        $hours = $report->getHours();
        $this->assertEquals( self::COUNT, $hours[0]->getCount('clicks'), 'month 1\'s clicks should equal 5' );
    }
    
    protected function logHour( $hour, $day, $month, $year, $times )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
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