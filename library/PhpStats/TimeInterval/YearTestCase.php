<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_YearTestCase extends PhpStats_TimeInterval_TestCase
{
    protected function getYear()
    {
        return new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
    }
}