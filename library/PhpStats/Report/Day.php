<?php
/** A collection of Hour intervals for a specific day */
class PhpStats_Report_Day extends PhpStats_Report_Abstract
{
    /** @return array of PhpStats_Report_Hour */
    public function getHours()
    {
        $hours = array();
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $timeParts = $this->timeParts;
            $timeParts['hour'] = $hour;
            $hours[ $hour ] = new PhpStats_Report_Hour( $timeParts, $this->attributes );
        }
        return $hours;
    }
    
}
