<?php
class SampleData
{
//    public function data()
//    {
//        for( $year = 2010; $year <= 2010; $year++ )
//        {
//            for( $month = 1; $month <= 1; $month++ )
//            {
//                for( $day = 1; $day <= 3; $day++ )
//                {
//                    $this->dataForDay( $day, $month, $year );
//                }
//            }
//        }   
//    }
//    
//    
    
    public function logHit( $hour, $minute, $second, $day, $month, $year, $times, $attributes = array() )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
        {
            $time = mktime( $hour, $minute, $second, $day, $month, $year );
            $logger = new PhpStats_Logger();
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