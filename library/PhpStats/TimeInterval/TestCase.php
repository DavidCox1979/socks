<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
abstract class PhpStats_TimeInterval_TestCase extends PhpStats_UnitTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    protected function now()
    {
        $timeParts = array(
        	'hour'=>date('G'),
            'day' => date('j'),
            'month' => date('n'),
            'year' => date('Y')
        );
        return $timeParts;
    }
    
    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
}