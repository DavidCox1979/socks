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
    
    protected function logThisDayWithHour( $hour, $attributes = array(), $eventType = 'click' )
    {
        $this->logHourDeprecated( $hour, self::DAY, self::MONTH, self::YEAR, 1, $attributes, $eventType );
    }
    
    protected function clearUncompactedEvents(  )
    {
        $this->db()->query('truncate table `socks_month_event`');
        $this->db()->query('truncate table `socks_day_event`');
        $this->db()->query('truncate table `socks_hour_event`');
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }
}