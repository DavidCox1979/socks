<?php
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    
    public function getUncompactedCount( $eventType )
    {
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
    
    protected function getDay( $day )
    {
        return new PhpStats_TimeInterval_Day( array(
            'year' => $this->timeParts['year'],
            'month' => $this->timeParts['month'],
            'day' => $day
        ));
    }
}