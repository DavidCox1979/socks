<?php
/** Reports are "partitioned" by their time intervals & custom attributes */
abstract class PhpStats_TimeInterval_Abstract implements PhpStats_TimeInterval
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
    
    protected function filterByHour()
    {
        $this->filterByDay();
        $this->select->where( 'hour = ?', $this->timeParts['hour'] ) ;
    }
    
    protected function filterByDay()
    {
        $this->filterByMonth();
        $this->select->where( 'day = ?', $this->timeParts['day'] ) ;
    }
    
    protected function filterByMonth()
    {
        $this->filterByYear();
        $this->select->where( 'month = ?', $this->timeParts['month'] );
    }
    
    protected function filterByYear()
    {
        $this->select->where( 'year = ?', $this->timeParts['year'] );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}