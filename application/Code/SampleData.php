<?php
class SampleData
{
    public function logHit( $hour, $minute, $second, $day, $month, $year, $times, $attributes = array(), $type = 'click' )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
        {
            $time = mktime( $hour, $minute, $second, $month, $day, $year );
            $logger = new PhpStats_Logger();
            $logger->log( $type, null, $attributes, $time );
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