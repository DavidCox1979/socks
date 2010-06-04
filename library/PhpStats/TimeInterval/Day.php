<?php
/**
* A collection of Hour intervals for a specific day
* 
* @license This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Day extends PhpStats_TimeInterval_Abstract
{
	protected $hours = array();
	
	/** @var string name of this interval (example hour, day, month, year) */
    protected $interval = 'day';
	
	/** @return array of PhpStats_TimeInterval_Hour */
	function getHours( $attributes = array() )
	{
		$attributes = ( 0 == count( $attributes ) ) ? $this->getAttributes() : $attributes;
		$attributesKey = md5(serialize($attributes));
		if( isset($this->hours[$attributesKey]) )
		{
			return $this->hours[$attributesKey];
		}
		$this->hours[$attributesKey] = array();
		for( $hour = 0; $hour <= 23; $hour++ )
		{
			$timeParts = $this->timeParts;
			$timeParts['hour'] = $hour;
			$this->hours[$attributesKey][ $hour ] = new PhpStats_TimeInterval_Hour( $timeParts, $attributes, $this->autoCompact, $this->allowUncompactedQueries );
		}
		return $this->hours[$attributesKey];
	}
	
	/** Ensures all of this day's hours intervals have been compacted */
	function compactChildren()
	{
		if( $this->isInPast() && $this->hasBeenCompacted() )
		{
			return;
		}
		foreach( $this->getHours() as $hour )
		{
			if( $hour->canCompact() )
			{
				$hour->compact();
			}
		}
	}
	
	/** @return boolean wether or not this time interval has been previously compacted */
	function hasBeenCompacted()
	{
		if( !is_null($this->has_been_compacted))
		{
			return $this->has_been_compacted;
		}
		$select = $this->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`hour` IS NULL' )
		    ->filterByDay( $this->getTimeParts() );
		if( $select->query()->fetchColumn() )
		{
			$this->has_been_compacted = true; 
			return true;
		}
		$this->has_been_compacted = false; 
		return false;
	}
	
	/**
	* Example:
	* SELECT SUM(`count`), event_type, `unique`, pageTBL.value pageValue, locTBL.value loc FROM `socks_hour_event`		
	* LEFT JOIN socks_hour_event_attributes pageTBL ON pageTBL.event_id = socks_hour_event.id and pageTBL.key = 'page'
	* LEFT JOIN socks_hour_event_attributes locTBL ON locTBL.event_id = socks_hour_event.id and locTBL.key = 'location'
	* WHERE (`year` = 2010) AND (`month` = 3) AND (`day` = 14) GROUP BY pageTBL.value, locTBL.value
	*/
	protected function doCompactAttributes( $table )
	{
		$attributeKeys = $this->describeAttributeKeys();
		
		$hourEventTbl = $this->table('hour_event');
		$hourEventAttributesTbl = $this->table('hour_event_attributes');
		
		$cols = array(
			'count' => 'SUM(`count`)',
			'event_type',
			'unique'
		);
		$select = $this->select()
			->from( $hourEventTbl, $cols );
		
		// join & group on each attribute we are segmenting the report by
		foreach( $attributeKeys as $attribute )
		{	
			$alias = $attribute.'TBL';
			$cond = sprintf( '%s.event_id = %s.id', $alias, $hourEventTbl );
			$cond .= sprintf( " AND %s.`key` = '%s'", $alias, $attribute );
			$select
				->joinLeft( array( $alias => $hourEventAttributesTbl ), $cond, array( $attribute => 'value' ) )
				->group( sprintf('%s.value',$alias) );
		}
		
		// "pivot" (group) on the unique column, so we get uniques and non uniques seperately
		$select->group( sprintf('%s.unique', $hourEventTbl ) );
		
		// also "pivot" the data on the event_type column so we get them back seperate
		$select->group( sprintf('%s.event_type', $hourEventTbl ) );
		
		$select->filterByDay( $this->getTimeParts() );
		
		$result = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_OBJ );
		foreach( $result as $row )
		{
			// insert record into day_event
			$bind = $this->getTimeParts();
			$bind['event_type'] = $row->event_type;
			$bind['unique'] = $row->unique;
			$bind['count'] = $row->count;
            $bind['attribute_keys'] = implode( ',', $attributeKeys );
            
            /** @todo duplicate in month */
            // attribute values
            $attributeValues = '';
            foreach( $attributeKeys as $attribute )
            {
                $value = $row->$attribute;
                $code = ':' . $attribute . ':' . $value . ';';
                $attributeValues .= $code;
            }
            $bind['attribute_values'] = $attributeValues;
            
			$this->db()->insert( $this->table('day_event'), $bind );
			
			// get the eventId
			$eventId = $this->db()->lastInsertId();
			
			// insert record(s) into day_event_attributes
			foreach( $attributeKeys as $attribute )
			{
				$bind = array(
					'event_id' => $eventId,
					'key' => $attribute,
					'value' => $row->$attribute
				);
				$attributeTable = $this->table('day_event_attributes');
				$this->db()->insert( $attributeTable, $bind );
			}
		}
	}
    
    protected function doCompactAttribute( $table, $eventType, $attributes )
	{
		throw new Exception();
	}

	protected function hasZeroCount()
	{
		if( $this->isInFuture() )
		{
			return true;
		}
		// has hits in day_event?
		if( 0 < $this->getCompactedCount() )
		{
			return false;
		}
		if( $this->hasBeenCompacted() )
		{
			// has no hits
			return true;
		}
		if( 0 < $this->getUncompactedCount() )
		{
			return false;
		}
	}
	
	/**
	* An additive value represented by summing this day's children hours
	* 
	* @param string $eventType
	* @param array $attributes
	* @param boolean $unique
	* 
	* @return integer
	*/
	function getUncompactedCount( $eventType = null, $attributes = array(), $unique = false )
	{
		if( $this->isInFuture() )
		{
			return 0;
		}
		if( !$this->allowUncompactedQueries )
		{
			return 0;
		}
		
		$attributes = count($attributes) ? $attributes : $this->getAttributes();
		$select = $this->select();
		if( !$this->childrenAreCompacted() )
		{
			$select->from( $this->table('event'), $unique ? 'count(DISTINCT(`host`))' : 'count(*)' );
			$this->addUncompactedAttributesToSelect( $select, $attributes );
		}
		else
		{
			$select->from( $this->table('hour_event'), 'SUM(`count`)' )
				->where( '`unique` = ?', $unique ? 1 : 0 );
			$this->addCompactedAttributesToSelect( $select, $attributes, 'hour' );
		}
        $select->filterByDay( $this->getTimeParts() )
            ->filterByEventType( $eventType );
		$count = (int)$select->query()->fetchColumn();
		return $count;
	}
	
	/** @todo refactor with someChildrenCompacted */
	function childrenAreCompacted()
	{
		$select = $this->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`hour` IS NOT NULL' );

		$select->filterByDay( $this->getTimeParts() );
		if( 24 == $select->query()->fetchColumn() )
		{
			return true;
		}
		return false;
	}
	
    function someChildrenCompacted()
	{
		$select = $this->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`hour` IS NOT NULL' );
		$select->filterByDay( $this->getTimeParts() );
		if( 0 < $select->query()->fetchColumn() )
		{
			return true;
		}
		return false;
	}
	
	/** @return integer cached value forced read from day_event table */
	function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
	{
		$attribs = count($attributes) ? $attributes : $this->getAttributes();
		$select = $this->select()
			->from( $this->table('day_event'), 'SUM(`count`)' )
			->where( '`unique` = ?', $unique ? 1 : 0 );
			
		if( !is_null( $eventType ) )
		{
			$select->where( 'event_type = ?', $eventType );
		}

		$select->filterByDay( $this->getTimeParts() );
		if( count($attribs))
		{
			$this->addCompactedAttributesToSelect( $select, $attribs );
		}
		return (int)$select->query()->fetchColumn();
	}
	
	/** @return string label for this day (example January 1st 2005) */
	function dayLabel()
	{
		$time = mktime( 1, 1, 1, $this->timeParts['month'], $this->timeParts['day'], $this->timeParts['year'] );
		$date = new Zend_Date( $time );
		return $date->toString( Zend_Date::DATE_FULL );
	}
	
	function dayShortLabel()
	{
		$time = mktime( 1, 1, 1, $this->timeParts['month'], $this->timeParts['day'], $this->timeParts['year'] );
		$date = new Zend_Date( $time );
		return $date->toString( Zend_Date::DAY_SHORT );
	}
	
	function isInPast()
	{
		$now = new Zend_Date();
		if( $now->toString( Zend_Date::YEAR ) > $this->timeParts['year'] )
		{
			return true;
		}
		if( $now->toString( Zend_Date::MONTH ) > $this->timeParts['month'] )
		{
			return true;
		}
		if( $now->toString( Zend_Date::DAY ) > $this->timeParts['day'] )
		{
			return true;
		}
		return false;
	}
	
	function isInPresent()
	{
		$now = new Zend_Date();
		return( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] &&
			$now->toString( Zend_Date::MONTH ) == $this->timeParts['month']  &&
			$now->toString( Zend_Date::DAY ) == $this->timeParts['day']
		);
	}
	
	function isInFuture( $now = null )
	{
		if( is_null($now) )
        {
        	$now = new Zend_Date();
		}
		if( $now->toString( Zend_Date::YEAR ) > $this->timeParts['year'] )
		{
			return false;
		}
		if( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] && $now->toString( Zend_Date::MONTH ) > $this->timeParts['month'] )
		{
			return false;
		}
		if( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] && $now->toString( Zend_Date::MONTH ) == $this->timeParts['month'] && $now->toString( Zend_Date::DAY ) >= $this->timeParts['day'] )
		{
			return false;
		}
		return true;
	}
	
	function getTimeParts()
	{
		$return = array();
		$return['day'] = $this->timeParts['day'];
		$return['month'] = $this->timeParts['month'];
		$return['year'] = $this->timeParts['year'];
		return $return;
	}
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension **/
    function describeAttributesValues( $eventType = null )
    {
        if( !$this->hasBeenCompacted() )
        {
            return $this->describeAttributesValuesHour($eventType);
        }
        return $this->doDescribeAttributeValues( 'day', $eventType );
    }
	
	/** @todo duplicated in month */
	function describeSingleAttributeValues( $attribute, $eventType = null )
	{
		if($this->hasBeenCompacted())
        {
            $values = $this->describeAttributesValues($eventType);
            return $values[$attribute];
        }
        
        if( isset($this->attribValues[$eventType][$attribute]) && !is_null($this->attribValues[$eventType][$attribute]))
		{
			return $this->attribValues[$eventType][$attribute];
		}
		$this->attribValues[$eventType][$attribute] = array();
		foreach( $this->doDescribeSingleAttributeValues( $attribute, $eventType ) as $row )
		{
			if( !is_null( $row[0] ) )
			{
				array_push( $this->attribValues[$eventType][$attribute], $row[0] );
			}
		}
		return $this->attribValues[$eventType][$attribute];
	}
	
	protected function doDescribeSingleAttributeValues( $attribute, $eventType )
	{
		// if enumerating an attribute we are filtering on, the only thing to return would be that particular filter's current value.
        $attributes = $this->getAttributes();
        if( $attributes[$attribute] )
        {
            return array($attributes[$attribute]);
        }
        
        $select = $this->describeAttributeValueSelect( $attribute );
		$select->filterByDay( $this->getTimeParts() )
		    ->filterByEventType( $eventType );
        $select = preg_replace( '#FROM `(.*)`#', 'FROM `$1` FORCE INDEX (key_2)', $select, 1 );
		return $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_NUM );
	}
	
	protected function describeAttributeValueSelect( $attribute )
	{
		if( $this->hasBeenCompacted() )
		{
            throw new Exception();
            //return $this->doDescribeAttributeValueSelect( $attribute, 'day' );
		}
		else if( $this->childrenAreCompacted() )
		{
			return $this->doDescribeAttributeValueSelect( $attribute, 'hour' );
		}
		else
		{
			return $this->doDescribeAttributeValueSelect( $attribute );
		}	
	}

	protected function doDescribeAttributeValueSelect( $attribute, $table = '' )
	{
		$select = $this->select()
			->from( $this->attributeTable($table), 'distinct(`value`)' )
			->where( '`key` = ?', $attribute );
			
		$this->joinEventTableToAttributeSelect( $select, $table );
		
		if( $table )
		{
			if( $this->hasAttributes() )
		    {
			    $this->addCompactedAttributesToSelect( $select, $this->getAttributes(), $table, false );
			}
		}
		else
		{
			$this->addUncompactedAttributesToSelect( $select, $this->getAttributes() );
		}
		return $select;
	}
	
    /** @todo make explicit */
	protected function describeAttributeKeysSql( $eventType = null )
	{
		if( $this->hasBeenCompacted() )
		{
			$select = $this->describeAttributeKeysSelect('day');
		}
		else if( $this->someChildrenCompacted() )
		{
			$select = $this->describeAttributeKeysSelect('hour');
		}
		else
		{
			$select = $this->describeAttributeKeysSelect();
		}
		$select->filterByDay( $this->getTimeParts() )
		    ->filterByEventType( $eventType );
		return $select;
	}
	
	protected function describeEventTypeSql()
	{
		$select = $this->select();
		$tablePrefix = $this->hasBeenCompacted() ? 'day' : 'hour';
		$select->from( $this->eventTable($tablePrefix), 'distinct(`event_type`)' );
		$select->filterByDay( $this->getTimeParts() );
		return $select;
	}
    
    function describeAttributeKeys( $eventType = null )
    {
        if( !$this->hasBeenCompacted() )
        {
             return parent::describeAttributeKeys($eventType);
        }
        
        $select = $this->select()
            ->from( 'socks_day_event', array('DISTINCT( attribute_keys )') );
        $select->filterByDay( $this->getTimeParts() );
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        $keys = array();
        foreach( $rows as $row )
        {
            foreach( explode(',', $row[0] ) as $key )
            {
                if( !empty($key) )
                {
                    array_push( $keys, $key );
                }
            }
        }
        return $keys;
    }
    
    protected function addCompactedAttributesToSelect( $select, $attributes, $table = 'day', $addNulls = true )
    {
        if( 'hour' == $table )
        {
            return parent::addCompactedAttributesToSelect( $select, $attributes, $table, $addNulls );
        }
        
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            if( !$addNulls && is_null($value) )
            {
                continue;
            }
            $code = ':' . $attribute . ':' . $value . ';';
            $select->where( $this->table($table.'_event') . ".attribute_values LIKE '%{$code}%'");
        }
        
    }
    
}