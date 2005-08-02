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
        $this->setTimeParts( $timeParts );
        $this->attributes = $attributes;
    }
    
    public function getTimeParts()
    {
        return $this->timeParts;
    }
    
    /** @throws PhpStats_TimeInterval_Exception_MissingTime */
    protected function setTimeParts( $timeParts )
    {
        $this->timeParts = $timeParts;
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
            $count = $this->getUncompactedCount( $eventType, $this->attributes );
            if( $this->isInPast() && 0 != $count )
            {
                $this->compact();
            }
        }
        return $count;
    }
    
    /** @return array of distinct event_types that have been used during this TimeInterval */
    public function describeEventTypes()
    {
        $this->compactChildren();
        $select = $this->describeEventTypeSql();
        $rows = $select->query( Zend_Db::FETCH_OBJ )->fetchAll();
        $eventTypes = array();
        foreach( $rows as $row )
        {
            array_push( $eventTypes, $row->event_type );
        }
        return $eventTypes;
    }
    
    /** @return array of the distinct attribute keys used for this time interval */
    public function describeAttributeKeys()
    {
        $this->compactChildren();
        $select = $this->describeAttributeKeysSql();
        $attributes = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $attributes, $row[0] );
        }
        return $attributes;
    }
    
    protected function isInPast() { return false; }
    
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
    
    protected function doCompact( $table )
    {
        foreach( $this->describeEventTypes() as $eventType )
        {
            $bind = $this->getTimeParts();
            $bind['event_type'] = $eventType;
            $bind['count'] = $this->getUncompactedCount( $eventType );
            $this->db()->insert( $table, $bind );
        }
    }
    protected function doCompactAttributes( $table )
    {
        $attributeValues = $this->describeAttributesValues();
        foreach( $this->describeEventTypes() as $eventType )
        {
            foreach( $attributeValues as $attribute => $values )
            {
                foreach( $values as $value )
                {
                    $this->doCompactAttribute( $table, $eventType, $attribute, $value );    
                }
            }
        }
    }
    
    protected function doCompactAttribute( $table, $eventType, $attribute, $value )
    {
        $count = $this->getUncompactedCount( $eventType, array( $attribute => $value ) );
        
        $bind = $this->getTimeParts();
        $bind['event_type'] = $eventType;
        $bind['count'] = $count;
        $this->db()->insert( $table, $bind );
        
        $bind = array(
            'event_id' => $this->db()->lastInsertId(),
            'key' => $attribute,
            'value' => $value
        );
        $this->db()->insert( $table . '_attributes', $bind );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
    
    protected function compactChildren()
    {
    }
    
    abstract protected function describeEventTypeSql();
    abstract protected function describeAttributeKeysSql();
}