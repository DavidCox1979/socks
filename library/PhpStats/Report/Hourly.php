<?php
class PhpStats_Report_Hourly extends PhpStats_Report_Abstract
{
    /**
    * @param string $eventType ( ex. click, search_impression )
    * @return array of PhpStats_Report_Hour
    */
    public function getHours()
    {
        $hours = array();
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $timeParts = $this->timeParts;
            $timeParts['hour'] = $hour;
            array_push( $hours, new PhpStats_Report_Hour( $timeParts, $this->attributes ) );
        }
        return $hours;
    }
    
}
