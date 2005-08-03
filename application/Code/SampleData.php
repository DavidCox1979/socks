<?php
class SampleData
{
    public function data()
    {
    }
    
    public function logHit( $hour, $minute, $second, $day, $month, $year, $times, $attributes = array() )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
        {
            $time = mktime( $hour, $minute, $second, $day, $month, $year );
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

$sampleData = new SampleData;
$sampleData->data();