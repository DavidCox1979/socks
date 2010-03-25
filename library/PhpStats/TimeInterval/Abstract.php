<?php
/**
* Reports are "partitioned" by their time intervals & custom attributes
* 
* @license This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
abstract class PhpStats_TimeInterval_Abstract extends PhpStats_Abstract implements PhpStats_TimeInterval
{
    /** @var array */
    protected $timeParts;
    
    /** @var array */
    protected $attributes;
    
    /** @var Zend_Db_Select */
    protected $select;
    
    /** @var bool */
    protected $autoCompact;
    protected $allowUncompactedQueries;
    
    /** @var mixed - null or array */
    protected $attribValues;
    protected $attribValuesAll;
    protected $attribKeys;
    
    protected $in_process_of_getting_attributes = false;
    
    /**
    * @param array $timeparts (hour, month, year, day )
    * @param array $attributes only records that match these
    *   attributes & values will be included in the report
    */
    public function __construct( $timeParts, $attributes = array(), $autoCompact = true, $allowUncompactedQueries = true )
    {
        $this->autoCompact = $autoCompact;
        $this->allowUncompactedQueries = $allowUncompactedQueries;
        $this->setTimeParts( $timeParts );
        $this->attributes = $attributes;
    }
    
    public function hasAttributes()
    {
		if( !is_array($this->getAttributes()) )
		{
			return false;
		}
		foreach( $this->getAttributes() as $value )
		{
			if( $value )
			{
				return true;
			}
		}
		return false;
    }
    
    public function getAttributes()
    {
        if( $this->in_process_of_getting_attributes )
        {
            return;
        }
        $this->in_process_of_getting_attributes = true;
        if( $this->hasZeroCount() )
        {
            return;
        }
        foreach( $this->describeAttributeKeys() as $attribute )
        {
            if( !isset( $this->attributes[$attribute] ) )
            {
                $this->attributes[$attribute] = null;
            }
        }
        $this->in_process_of_getting_attributes = false;
        return $this->attributes;
    }
    
    protected function hasZeroCount()
    {
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
    * @param array of attributes (not implemented, set thru constructor instead)
    * @param boolean $unique set to true to count each hostname/IP Address only once. Defaults to false.
    * 
    * @return integer additive value
    */
    public function getCount( $eventType = null, $attributes = array(), $unique = false )
    {
        if( count($attributes))
        {
            throw new Exception('not implemented, set thru constructor instead');
        }
        
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return $this->getCompactedCount( $eventType, array(), $unique );   
        }
        
        $count = $this->getUncompactedCount( $eventType, $this->attributes, $unique );
        if( $this->shouldCompact() && $this->autoCompact )
        {
            $this->compact();
        }
        return $count;
    }
    
    /** @return array of distinct event_types that have been used during this TimeInterval */
    public function describeEventTypes()
    {
    	if( $this->notCompactedAndCannotHitUncompactedTable() )
    	{
			return array();
    	}
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
    public function describeAttributeKeys( $eventType = null )
    {
    	if( isset( $this->attribKeys[$eventType] ) && !is_null( $this->attribKeys[$eventType] ) )
    	{
			return $this->attribKeys[$eventType];
    	}
        if( $this->autoCompact && !$this->hasBeenCompacted() )
        {
            $this->compactChildren();
        }
        if( $this->notCompactedAndCannotHitUncompactedTable() )
    	{
			return array();
    	}
        $select = $this->describeAttributeKeysSql( $eventType );
        $this->attribKeys[$eventType] = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $this->attribKeys[$eventType], $row[0] );
        }
        return $this->attribKeys[$eventType];
    }
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension */
    public function describeAttributesValues( $eventType = null )
    {
        if( !is_null($this->attribValuesAll[$eventType]))
        {
            return $this->attribValuesAll[$eventType];
        }
        if( $this->notCompactedAndCannotHitUncompactedTable() )
    	{
			return array();
    	}
        $attributes = $this->describeAttributeKeys();
        $this->attribValues = array();
        $this->attribValues[$eventType] = array();
        foreach( $attributes as $attribute )
        {
            if( !isset($this->attribValues[$eventType][ $attribute ]) || is_null($this->attribValues[$eventType][ $attribute ]))
            {
                $this->attribValuesAll[$eventType][ $attribute ] = $this->describeSingleAttributeValues( $attribute, $eventType );
            }
            else
            {
                $this->attribValuesAll[$eventType][$attribute] = $this->attribValues[$attribute];
            }
        }
        return $this->attribValuesAll[$eventType];
    }
    
    public function describeAttributesValuesCombinations( $eventType = null )
    {
        return $this->pc_array_power_set( $this->describeAttributeKeys(), $eventType );
    } 
    
    public function isInPast()
    {
        return false;
    }
    
    public function autoCompact()
    {
		return $this->autoCompact;
    }
    
    public function compactChildren()
    {	
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
    abstract public function hasBeenCompacted();
    
    /** @return integer cached value forced read from compacted table */
    abstract public function getCompactedCount( $eventType = null, $attributes = array(), $unique = false ); 
    
    /** @return integer value forced read from uncompacted table */
    abstract public function getUncompactedCount( $eventType = null, $attributes = array(), $unique = false );
    
    protected function filterByHour()
    {
        $this->filterByDay();
        $this->select->where( '`hour` = ?', $this->timeParts['hour'] ) ;
    }
    
    protected function filterByDay()
    {
        $this->filterByMonth();
        $this->select->where( '`day` = ?', $this->timeParts['day'] ) ;
    }
    
    protected function filterByMonth()
    {
        $this->filterByYear();
        $this->select->where( '`month` = ?', $this->timeParts['month'] );
    }
    
    protected function filterByYear()
    {
        $this->select->where( '`year` = ?', $this->timeParts['year'] );
    }
    
    protected function doCompact( $table )
    {
        foreach( $this->describeEventTypes() as $eventType )
        {
            // non-unique
            $bind = $this->getTimeParts();
            $bind['event_type'] = $eventType;
            $bind['count'] = $this->getUncompactedCount( $eventType );
            $this->db()->insert( $this->table($table), $bind );
            
            // unique
            $bind = $this->getTimeParts();
            $bind['event_type'] = $eventType;
            $bind['count'] = $this->getUncompactedCount( $eventType, array(), true );
            $bind['unique'] = 1;
            $this->db()->insert( $this->table($table), $bind );
        }
    }
    
    protected function doCompactAttributes( $table )
    {
        foreach( $this->describeEventTypes() as $eventType )
        {
            $valueCombos = $this->describeAttributesValuesCombinations( $eventType );
            foreach( $valueCombos as $valueCombo )
            {
                $this->doCompactAttribute( $table, $eventType, $valueCombo );
            }
        }
    }
    
    protected function doCompactAttribute( $table, $eventType, $attributes )
    {
        $count = $this->getUncompactedCount( $eventType, $attributes, false );
        if( 0 == $count )
        {
            return;
        }
        $countUnique = $this->getUncompactedCount( $eventType, $attributes, true );
        
        // non - unique
        $bind = $this->getTimeParts();
        $bind['event_type'] = $eventType;
        $bind['unique'] = 0;
        $bind['count'] = $count;
        $this->db()->insert( $this->table($table), $bind );
        $eventId = $this->db()->lastInsertId();
        
        // unique
        $bind['unique'] = 1;
        $bind['count'] = $countUnique;
        $this->db()->insert( $this->table($table), $bind );
        $uniqueEventId = $this->db()->lastInsertId();
        
        foreach( array_keys( $attributes) as $attribute )
        {
        	// non-unique's attributes
            $bind = array(
                'event_id' => $eventId,
                'key' => $attribute,
                'value' => $attributes[$attribute]
            );
            $attributeTable = $this->table($table.'_attributes');
            $this->db()->insert( $attributeTable, $bind );
            
            // unique's attributes
            $bind = array(
                'event_id' => $uniqueEventId,
                'key' => $attribute,
                'value' => $attributes[$attribute]
            );
            $attributeTable = $this->table($table.'_attributes');
            $this->db()->insert( $attributeTable, $bind );
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
    
    protected function markAsCompacted()
    {
        if( !$this->hasBeenCompacted() )
        {
            $this->has_been_compacted = true;
            $this->db()->insert( $this->table('meta'), $this->getTimeParts() );
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
    
    protected function pc_array_power_set( $array, $eventType = null )
    {
        // initialize by adding the empty set
        $results = array(array( ));

        foreach ($array as $element)
        {
            foreach ($results as $combination)
            {
                foreach( $this->describeSingleAttributeValues( $element, $eventType ) as $value )
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
    
    protected function shouldCompact()
    {
        return true;
    }
    
    protected function addUncompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = $this->getUncompactedFilterByAttributesSubquery( $attribute, $value, $this->table('event_attributes') );
            $this->select->where( sprintf( '%s.id IN( %s )', $this->table('event'), (string)$subQuery ) );
        }
    }
    
    protected function addCompactedAttributesToSelect( $attributes, $table = 'day', $addNulls = true )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            if( is_null($value) && !$addNulls )
        	{
				continue;
        	}
        	$subQuery = $this->getFilterByAttributesSubquery( $attribute, $value, $this->table( $table.'_event_attributes') );
            $this->select->where( $this->table($table.'_event').'.id IN (' . (string)$subQuery . ')' );
        }
    }
    
    protected function getUncompactedFilterByAttributesSubquery( $attribute, $value, $table )
    {
        $subQuery = $this->db()->select();
        $subQuery->from( $table, 'DISTINCT(event_id)' );

        if( $table != 'event_attributes' || !is_null($value) )
        {
            $this->doFilterByAttributesUncompacted( $subQuery, $attribute, $value );
        }

        return $subQuery;
    }
    
    protected function doFilterByAttributesUncompacted( $select, $attributeKey, $attributeValue )
    {
        if( is_null( $attributeValue ) )
        {

        }
        else
        {
            $select->where( sprintf( '`key` = %s && `value` = %s',
                $this->db()->quote( $attributeKey ),
                 $this->db()->quote( $attributeValue )
            ));
        }
    }
    
    protected function joinEventTableToAttributeSelect( $tablePrefix = '' )
    {
        if( $tablePrefix )
        {
            $tablePrefix = $tablePrefix . '_';
        }
        $eventTable = $this->table( $tablePrefix.'event' );
        $attribTable = $this->table( $tablePrefix.'event_attributes' );
        $joinCond = sprintf( '%s.id = %s.event_id', $eventTable, $attribTable );
        $this->select->joinLeft( $eventTable, $joinCond, array() );
    }
    
    abstract protected function describeEventTypeSql();
    abstract protected function describeAttributeKeysSql( $eventType = null );
    abstract protected function childrenAreCompacted();
    
    abstract public function describeSingleAttributeValues( $attribute, $eventType = null );
    
    private function notCompactedAndCannotHitUncompactedTable()
    {
		return !$this->autoCompact && !$this->hasBeenCompacted() && !$this->allowUncompactedQueries && !$this->childrenAreCompacted();
    }
}
