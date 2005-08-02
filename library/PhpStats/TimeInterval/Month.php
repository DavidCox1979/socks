<?php
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    
    public function getUncompactedCount( $eventType )
    {
        $count = 0;
        foreach( $this->getDays() as $day )
        {
            $count += $day->getCount( $eventType );
        }
        return $count;
    }
    
    public function getCompactedCount( $eventType )
    {
    }
    
    public function getDays()
    {
        $days = cal_days_in_month( CAL_GREGORIAN, $this->timeParts['month'], $this->timeParts['year'] );
        $return = array();
        for( $day = 1; $day <= $days; $day++ )
        {
            $return[ $day ] = $this->getDay( $day );
        }
        return $return;
    }
    
    public function monthLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], 1, $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::MONTH_NAME );
    }
    
    protected function getDay( $day )
    {
        return new PhpStats_TimeInterval_Day( array(
            'year' => $this->timeParts['year'],
            'month' => $this->timeParts['month'],
            'day' => $day
        ));
    }
    
    protected function describeEventTypeSql()
    {
    }
    
    protected function describeAttributeKeysSql()
    {
    }
    
    protected function doGetAttributeValues( $attribute )
    {    
    }
}