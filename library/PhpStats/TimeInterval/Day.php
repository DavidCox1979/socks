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
		return $this->has_been_compacted = (bool)$select->query()->fetchColumn();
	}
	
	protected function doCompactAttributes( $table )
	{
		$cols = array(
			'count' => 'SUM(`count`)',
			'event_type',
			'unique',
            'attribute_keys',
            'attribute_values'
		);
		$select = $this->select()
			->from( $this->table('hour_event'), $cols )
		    ->group('attribute_values')
            ->group( 'unique' )
            ->group( 'event_type' )
		    ->filterByDay( $this->getTimeParts() );
		
		$result = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_OBJ );
		foreach( $result as $row )
		{
			$bind = $this->getTimeParts();
			$bind['event_type'] = $row->event_type;
			$bind['unique'] = $row->unique;
			$bind['count'] = $row->count;
            $bind['attribute_keys'] = implode( ',', $this->describeAttributeKeys() );
            $bind['attribute_values'] = $row->attribute_values;
			$this->db()->insert( $this->table('day_event'), $bind );
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
		if( $this->isInFuture() || !$this->allowUncompactedQueries )
		{
			return 0;
		}
		
		$attribs = count($attributes) ? $attributes : $this->getAttributes();
		$select = $this->select();
		if( !$this->childrenAreCompacted() )
		{
			$select->from( $this->table('event'), $unique ? 'count(DISTINCT(`host`))' : 'count(*)' );
			$select->addUncompactedAttributes( $attribs );
		}
		else
		{
			$select->from( $this->table('hour_event'), 'SUM(`count`)' )
				->where( '`unique` = ?', $unique ? 1 : 0 )
			    ->addCompactedAttributes( $attribs, 'hour' );
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
			->where( '`hour` IS NOT NULL' )
            ->filterByDay( $this->getTimeParts() );
		return (bool) 24 == $select->query()->fetchColumn();
	}
	
    function someChildrenCompacted()
	{
		$select = $this->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`hour` IS NOT NULL' )
		    ->filterByDay( $this->getTimeParts() );
		return (bool) 0 < $select->query()->fetchColumn();
	}
	
	/** @return integer cached value forced read from day_event table */
	function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
	{
		$select = $this->select()
			->from( $this->table('day_event'), 'SUM(`count`)' )
			->where( '`unique` = ?', $unique ? 1 : 0 )
            ->filterByEventType( $eventType )
            ->filterByDay( $this->getTimeParts() )
		    ->addCompactedAttributes( count($attributes) ? $attributes : $this->getAttributes() );
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
        if( $this->hasBeenCompacted() )
        {
            return $this->doAttributeValues( 'day', $eventType );
        }
        if( $this->someChildrenCompacted() )
        {
            return $this->doAttributeValues( 'hour', $eventType  );
        }
        return $this->doAttributeValuesUncompacted( $eventType);        
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
        
        $select = $this->describeAttributeValueSelect( $attribute )
		    ->filterByDay( $this->getTimeParts() )
		    ->filterByEventType( $eventType );
        $select = preg_replace( '#FROM `(.*)`#', 'FROM `$1` FORCE INDEX (key_2)', $select, 1 );
		return $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_NUM );
	}
	
	protected function describeAttributeValueSelect( $attribute )
	{
		if( $this->hasBeenCompacted() )
		{
            throw new Exception();
            //return $this->doAttributeValueselect( $attribute, 'day' );
		}
		else if( $this->childrenAreCompacted() )
		{
			return $this->doAttributeValueselect( $attribute, 'hour' );
		}
		else
		{
			return $this->doAttributeValueselect( $attribute );
		}	
	}

	protected function doAttributeValueselect( $attribute, $table = '' )
	{
		$select = $this->select()
			->from( $this->attributeTable($table), 'distinct(`value`)' )
			->where( '`key` = ?', $attribute )
			->joinAttributesTable( $table );
		
		if( $table )
		{
			if( $this->hasAttributes() )
		    {
			    $select->addCompactedAttributes( $this->getAttributes(), $table, false );
			}
		}
		else
		{
			$select->addUncompactedAttributes( $this->getAttributes() );
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
		$tablePrefix = $this->hasBeenCompacted() ? 'day' : 'hour';
		$select = $this->select()
            ->from( $this->eventTable($tablePrefix), 'distinct(`event_type`)' )
		    ->filterByDay( $this->getTimeParts() );
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
    
}