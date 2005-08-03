<?php
abstract class PhpStats_TimeIntervalTestCase extends PhpStats_UnitTestCase
{
    protected function logHour( $hour, $day, $month, $year, $times, $attributes = array(), $type = 'click' )
    {
        $sampleData = new SampleData;
        $sampleData->logHit( $hour, $this->minute(), $this->second(), $day, $month, $year, $times, $attributes, $type );
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