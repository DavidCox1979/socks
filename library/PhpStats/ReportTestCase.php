<?php
class PhpStats_ReportTestCase extends PhpStats_UnitTestCase
{
    const EVENTS_PER_HOUR = 5;
    
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