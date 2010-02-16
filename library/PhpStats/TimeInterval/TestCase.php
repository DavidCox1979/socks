<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
abstract class PhpStats_TimeInterval_TestCase extends PhpStats_UnitTestCase
{
    protected function logHour( $hour, $day, $month, $year, $times, $attributes = array(), $type = 'click', $hostname = null )
    {
        $sampleData = new SampleData;
        $sampleData->logHit( $hour, $this->minute(), $this->second(), $day, $month, $year, $times, $attributes, $type, $hostname );
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