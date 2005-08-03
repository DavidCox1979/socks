<?php
/** Reports are "partitioned" by their time intervals & custom attributes */
abstract class PhpStats_Report_Abstract implements PhpStats_Report
{
    /** @var array */
    protected $timeParts;
    
    /** @var array */
    protected $attributes;
    
    /** @var Zend_Db_Select */
    protected $select;
    
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
    
    public function getTimeParts()
    {
        return $this->timeParts;
    }
    
    /**
    * Gets the number of records for this hour, event type, and attributes
    * 
    * Uses the hour_event aggregrate table if it has a value,
    * otherwise it uses count(*) queries on the event table.
    * 
    * @param string $eventType
    * @return integer additive value
    */
    public function getCount( $eventType )
    {
        $count = $this->getCompactedCount( $eventType );   
        if( !$count )
        {
            $count = $this->getUncompactedCount( $eventType );
        }
        return $count;
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}