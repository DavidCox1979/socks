<?php
class PhpStats_Report_Hourly
{
    /** @var array */
    protected $timeParts;
    
    /** @var array */
    protected $attributes;
    
    /**
    * @param array $timeparts (hour, month, year, day )
    * @param array $attributes only records that match these
    *   attributes & values will be included in the report
    */
    public function __construct( $timeParts, $attributes = array() )
    {
        $this->timeParts = $timeParts;
        $this->attributes = $attributes;
    }
    
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