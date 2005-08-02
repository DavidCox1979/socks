<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
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
        foreach( $this->describeAttributeKeys() as $attribute )
        {
            if( !isset( $attributes[$attribute] ) )
            {
                $attributes[$attribute] = null;
            }
        }
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
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension */
    public function describeAttributesValues()
    {
        $attributes = $this->describeAttributeKeys();
        $return = array();
        foreach( $attributes as $attribute )
        {
            $return[ $attribute ] = $this->doGetAttributeValues( $attribute );
        }
        return $return;        
    }
    
    public function describeAttributesValuesCombinations()
    {
        return $this->pc_array_power_set( $this->describeAttributeKeys() );
    }
    
    function pc_array_power_set($array)
    {
        // initialize by adding the empty set
        $results = array(array( ));

        foreach ($array as $element)
        {
            foreach ($results as $combination)
            {
                foreach( $this->doGetAttributeValues( $element ) as $value )
                {
                    $merge = array_merge(array( $element => (string)$value ), $combination);
                    array_push($results, $merge);
                }
            }
        }
        
        // ensure null is set for empty ones
        foreach( $results as $index => $result )
        {
            foreach( $array as $attrib )
            {
                if( !isset( $results[$index][$attrib] ))
                {
                    $results[$index][$attrib] = null;
                }
            }
        }

        return $results;
    }
    
    protected function isInPast()
    {
        return false;
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
        $valueCombos = $this->describeAttributesValuesCombinations();
        foreach( $this->describeEventTypes() as $eventType )
        {
            foreach( $valueCombos as $valueCombo )
            {
                $this->doCompactAttribute( $table, $eventType, $valueCombo );    
            }
        }
    }
    
    protected function doCompactAttribute( $table, $eventType, $attributes )
    {
        $count = $this->getUncompactedCount( $eventType, $attributes );
        if( 0 == $count )
        {
            return;
        }
        
        $bind = $this->getTimeParts();
        $bind['event_type'] = $eventType;
        $bind['count'] = $count;
        
        $this->db()->insert( $table, $bind );
        $eventId = $this->db()->lastInsertId();
        foreach( array_keys( $attributes) as $attribute )
        {
            $bind = array(
                'event_id' => $eventId,
                'key' => $attribute,
                'value' => $attributes[$attribute]
            );
            $this->db()->insert( $table . '_attributes', $bind );
        }
    }
    
    protected function getFilterByAttributesSubquery( $attribute, $value, $table )
    {
        $subQuery = $this->db()->select();
        $subQuery->from( $table, 'DISTINCT(event_id)' );

        if( $table != 'event_attributes' || !is_null($value) )
        {
            $this->doFilterByAttributes( $subQuery, $attribute, $value );
        }

        return $subQuery;
    }
    
    protected function doFilterByAttributes( $select, $attributeKey, $attributeValue )
    {
        if( is_null( $attributeValue ) )
        {
            $select->where( sprintf( '`key` = %s && `value` IS NULL',
                $this->db()->quote( $attributeKey )
            ));
        }
        else
        {
            $select->where( sprintf( '`key` = %s && `value` = %s',
                $this->db()->quote( $attributeKey ),
                 $this->db()->quote( $attributeValue )
            ));
        }
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
    abstract protected function doGetAttributeValues( $attribute );
}