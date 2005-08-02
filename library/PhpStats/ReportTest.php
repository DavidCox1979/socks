<?php
class ReportTest extends PhpStats_UnitTestCase
{
    const EVENTS_PER_HOUR = 5;
    const MONTH = 1;
    const DAY = 1;
    const YEAR = 2005;
    
    function testReportHours()
    {
        $this->insertSampleData();
        $report = new PhpStats_Report( array(
            'hour' => 1,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
    }
    
    protected function insertSampleData()
    {
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $this->logHour( $hour );
        }
    }
    
    protected function logHour( $hour )
    {
        for( $repeat = 1; $repeat <= self::EVENTS_PER_HOUR; $repeat++ )
        {
            $time = mktime( $hour, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR );
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