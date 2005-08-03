<?php
/** Reports are "partitioned" by their time intervals & custom attributes */
interface PhpStats_Report
{
    /**
    * @param array $timeparts (hour, month, year, day )
    * @param array $attributes only records that match these
    *   attributes & values will be included in the report
    */
    public function __construct( $timeParts, $attributes = array() );
    
    public function getUncompactedCount( $eventType );
    
    public function getCompactedCount( $eventType );
    
    /**
    * Gets the number of records for this time interval, event type, and attributes
    * 
    * Uses highest abstracted aggregrate table if available,
    * otherwise it uses next lowest grain of aggregate table or the even table
    * 
    * @param string $eventType
    * @return integer additive value
    */
    public function getCount( $eventType );
}