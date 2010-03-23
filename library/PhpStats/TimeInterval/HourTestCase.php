<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourTestCase extends PhpStats_TimeInterval_TestCase
{
    
    const HOUR = 3;
    const DAY = 3;
    const MONTH = 3;
    const YEAR = 2005;
    
    const COUNT = 5;
    
    protected function clearUncompactedEvents()
    {
        $this->db()->query('truncate table `socks_event`'); // delete the records from the event table to force it to read from the hour_event table. 
    }
    
    protected function getTimeParts()
    {
        return array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
    
    protected function insertHitDifferentDay()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY - 1, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }
    
    protected function insertHitDifferentMonth()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }
    
    protected function now()
    {
        $timeParts = array(
            'hour' => date('G'),
            'day' => date('j'),
            'month' => date('n'),
            'year' => date('Y')
        );
        return $timeParts;
    }
    
    protected function timePartsPlusOneHour()
    {
		$timeParts = $this->getTimeParts();
		$timeParts['hour'] += 1;
		return $timeParts;
    }
    
    protected function timePartsPlusOneDay()
    {
		$timeParts = $this->getTimeParts();
		$timeParts['day'] += 1;
		return $timeParts;
    }
    
    protected function timePartsPlusOneMonth()
    {
		$timeParts = $this->getTimeParts();
		$timeParts['month'] += 1;
		return $timeParts;
    }
    
    protected function timePartsPlusOneYear()
    {
		$timeParts = $this->getTimeParts();
		$timeParts['year'] += 1;
		return $timeParts;
    }
}
