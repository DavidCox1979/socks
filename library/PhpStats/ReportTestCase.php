<?php
abstract class PhpStats_ReportTestCase extends PhpStats_UnitTestCase
{
    protected function logHour( $hour, $day, $month, $year, $times, $attributes = array() )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
        {
            $time = mktime( $hour, $this->minute(), $this->second(), $day, $month, $year );
            $logger = new Phpstats_Logger();
            $logger->log( 'click', $attributes, $time );
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