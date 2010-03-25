<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayTestCase extends PhpStats_TimeInterval_TestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( $this->getTimeParts() );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }
 
    protected function insertHitDifferentMonth()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }   
    
    protected function clearUncompactedEvents( $noHour = false )
    {
    	if( !$noHour )
    	{
	        $this->db()->query('truncate table `socks_hour_event`');
	        $this->db()->query('truncate table `socks_hour_event_attributes`');
		}
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }
    
    protected function logThisDayWithHour( $hour, $attributes = array(), $eventType = 'click' )
    {
        $this->logHourDeprecated( $hour, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes, $eventType );
    }
	
	protected function dayPlusOneDayTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOne = $day;
        $dayPlusOne['day'] += 1;
        return $dayPlusOne;
    }
    
    protected function dayPlusOneMonthTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOneMonth = $day;
        $dayPlusOneMonth['month'] += 1;
        return $dayPlusOneMonth;
    }
    
    protected function dayPlusOneYearTimeParts()
    {
		$day = $this->dayTimeParts();
		$dayPlusOneYear = $day;
        $dayPlusOneYear['year'] += 1;
        return $dayPlusOneYear;
    }
    
	protected function dayTimeParts()
    {
		$day = array(
        	'hour' => 1,
        	'day' => self::DAY,
        	'month' => self::MONTH,
        	'year' => self::YEAR
        );
        return $day;
    }
}