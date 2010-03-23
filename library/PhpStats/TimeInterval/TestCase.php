<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
abstract class PhpStats_TimeInterval_TestCase extends PhpStats_UnitTestCase
{
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
}