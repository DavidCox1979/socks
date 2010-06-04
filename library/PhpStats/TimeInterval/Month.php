<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    /** @var string name of this interval (example hour, day, month, year) */
    protected $interval = 'month';
    
    protected $days;
    
    /** Compacts all of this month's day intervals */
    function compactChildren()
    {
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return;
        }
        foreach( $this->getDays() as $day )
        {
            if( !$day->isInPast() || !$day->hasBeenCompacted() )
            {
                $day->compact();
            }
        }
    }
    
    /** @todo should be able to hit hour/day/month table */
    function getUncompactedCount( $eventType=null, $attributes = array(), $unique = false )
    {
    	$attributes = count( $attributes ) ? $attributes : $this->getAttributes();
    	if( !$this->allowUncompactedQueries )
    	{
			return 0;
    	}
        /** @todo inline */
    	$childrenAreCompacted = $this->childrenAreCompacted();
    	$select = $this->select();
        if( !$childrenAreCompacted )
        {
            if( $unique )
            {
                $select->from( $this->table('event'), 'count(DISTINCT(`host`))' );
            }
            else
            {
                $select->from( $this->table('event'), 'count(*)' );
            }
            $select->where( 'event_type = ?', $eventType );
            $select->filterByMonth($this->getTimeParts());
            /* @todo write test & uncoment */
            //$this->addUncompactedAttributesToSelect( $select, $attributes );
        }
        else
        {
            $select->from( $this->table('day_event'), 'SUM(`count`)' )
				->where( '`unique` = ?', $unique ? 1 : 0 )
			    ->filterByEventType( $eventType )
			    ->filterByMonth($this->getTimeParts())
			    ->addCompactedAttributes( $attributes, 'day' );
        }
        
        return (int)$select->query()->fetchColumn();
    }
    
    function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
		$select = $this->select()
			->from( $this->table('month_event'), 'SUM(`count`)' )
			->where( '`unique` = ?', $unique ? 1 : 0 )
			->filterByEventType( $eventType )
            ->filterByMonth($this->getTimeParts());
        if( count($this->getAttributes()))
		{
			$select->addCompactedAttributes( $this->getAttributes(), 'month' );
		}
		return (int)$select->query()->fetchColumn();
    }
    
    function getDays( $attributes = array() )
    {
        if( is_array( $this->days) && count($this->days) )
    	{
			return $this->days;
    	}
        /** @todo extract method */
        $numberOfDaysInMonth = cal_days_in_month( CAL_GREGORIAN, $this->timeParts['month'], $this->timeParts['year'] );
        $this->days = array();
        for( $day = 1; $day <= $numberOfDaysInMonth; $day++ )
        {
            $this->days[ $day ] = $this->getDay( $day, $attributes );
        }
        return $this->days;
    }
    
    function monthLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], 1, $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::MONTH_NAME );
    }
    
    function yearLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], 1, $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::YEAR );
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
	function hasBeenCompacted()
	{
		if( isset($this->has_been_compacted) )
		{
			return $this->has_been_compacted;
		}
		$select = $this->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`day` IS NULL' )
		    ->filterByMonth($this->getTimeParts());
		if( $select->query()->fetchColumn() )
		{
			$this->has_been_compacted = true; 
			return true;
		}
		$this->has_been_compacted = false; 
		return false;
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
        $bind['attribute_keys'] = implode( ',', array_keys($attributes) );
        
        /** @todo duplicate in day */
        // attribute values
        $attributeValues = '';
        foreach( $attributes as $key => $value )
        {
            $code = ':' . $key . ':' . $value . ';';
            $attributeValues .= $code;
        }
        $bind['attribute_values'] = $attributeValues;
        
        $this->db()->insert( $this->table($table), $bind );
        $eventId = $this->db()->lastInsertId();
        
        // unique
        $bind['unique'] = 1;
        $bind['count'] = $countUnique;
        $this->db()->insert( $this->table($table), $bind );
        $uniqueEventId = $this->db()->lastInsertId();
        
        foreach( array_keys($attributes) as $attribute )
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
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension **/
    function describeAttributesValues( $eventType = null )
    {
        if( $this->hasBeenCompacted() )
        {
            return $this->describeAttributesValuesHour($eventType);
        }
        if( !$this->someChildrenCompacted() )
        {
            return $this->describeAttributesValuesHour($eventType);
        }
        return $this->doDescribeAttributeValues( 'month', $eventType );
    }
    
    /**
    * @todo extract paramaterized method for each if branch.
    * @todo duplicated in day
    * @todo doesnt filter based on time interval
    */
    function describeSingleAttributeValues( $attribute, $eventType = null )
    {
        if( isset($this->attribValues[$eventType][$attribute]) && !is_null($this->attribValues[$eventType][$attribute]))
        {
            return $this->attribValues[$eventType][$attribute];
        }
        
        if( $this->hasBeenCompacted() )
        {
            $select = $this->select()
                ->from( $this->table('month_event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute )
                ->filterByEventType( $eventType )
                ->addCompactedAttributes( $this->getAttributes(), 'month', false );
            $this->joinEventTableToAttributeSelect( $select, 'month' );
        }
        else if( $this->someChildrenCompacted() )
        {
            $values = $this->describeAttributesValues($eventType);
            return $values[$attribute];
        }
        else
        {
            $select = $this->select()
                ->from( $this->table('event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute )
                ->filterByEventType( $eventType );
            $this->joinEventTableToAttributeSelect($select);
            $this->addUncompactedAttributesToSelect( $select, $this->getAttributes() );
        }
        
        $select = preg_replace( '#FROM `(.*)`#', 'FROM `$1` FORCE INDEX (key_2)', $select, 1 );
        
        $values = array();
        
        $rows = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_NUM );
        foreach( $rows as $row )
        {
            if( !is_null($row[0]) )
            {
                array_push( $values, $row[0] );
            }
        }
        return $values;
    }
    
    function isInFuture()
	{
		$now = new Zend_Date();
		if( $now->toString( Zend_Date::YEAR ) > $this->timeParts['year'] )
		{
			return false;
		}
		if( $now->toString( Zend_Date::MONTH ) >= $this->timeParts['month'] )
		{
			return false;
		}
		return true;
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
		return false;
	}
	
	function isInPresent()
	{
		$now = new Zend_Date();
		return( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] &&
			$now->toString( Zend_Date::MONTH ) == $this->timeParts['month']
		);
	}

	function getTimeParts()
	{
		$return = array();
		$return['month'] = $this->timeParts['month'];
		$return['year'] = $this->timeParts['year'];
		return $return;
	}
	
	/** @todo duplicated in day */
	protected function doCompactAttributes( $table )
	{
		$cols = array(
			'count' => 'SUM(`count`)',
			'event_type',
			'unique'
		);
		$select = $this->select()
			->from( $this->table('day_event'), $cols );
		
		// join & group on each attribute we are segmenting the report by
		foreach( $this->describeAttributeKeys() as $attribute )
		{	
			$alias = $attribute.'TBL';
			$cond = sprintf( '%s.event_id = %s.id', $alias, $this->table('day_event') );
			$cond .= sprintf( " AND %s.`key` = '%s'", $alias, $attribute );
			$select->joinLeft( array( $alias => $this->table('day_event_attributes') ), $cond, array( $attribute => 'value' ) )
				->group( sprintf('%s.value',$alias) );
			
		}
		
		// "pivot" (group) on the unique column, so we get uniques and non uniques seperately
		$select->group( sprintf('%s.unique', $this->table('day_event') ) );
		
		// also "pivot" the data on the event_type column so we get them back seperate
		$select->group( sprintf('%s.event_type', $this->table('day_event') ) );
		
		$select->filterByMonth($this->getTimeParts());
		
		$result = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_OBJ );
		foreach( $result as $row )
		{
			// insert record into month_event
			$bind = $this->getTimeParts();
			$bind['event_type'] = $row->event_type;
			$bind['unique'] = $row->unique;
			$bind['count'] = $row->count;
            $bind['attribute_keys'] = implode( ',', $this->describeAttributeKeys() );
            
            /** @todo duplicate in month */
            // attribute values
            $attributeValues = '';
            foreach( $this->describeAttributeKeys() as $attribute )
            {
                $value = $row->$attribute;
                $code = ':' . $attribute . ':' . $value . ';';
                $attributeValues .= $code;
            }
            $bind['attribute_values'] = $attributeValues;
            
			$this->db()->insert( $this->table('month_event'), $bind );
			
			// get the eventId
			$eventId = $this->db()->lastInsertId();
			
			// insert record(s) into month_event_attributes
			foreach( $this->describeAttributeKeys() as $attribute )
			{
				$bind = array(
					'event_id' => $eventId,
					'key' => $attribute,
					'value' => $row->$attribute
				);
				$this->db()->insert( $this->table('month_event_attributes'), $bind );
			}
		}
	}
	 
    protected function getDay( $day, $attributes = array() )
    {
        $attributes = count( $attributes ) ? $attributes : $this->getAttributes();
        $timeParts = array(
            'year' => $this->timeParts['year'],
            'month' => $this->timeParts['month'],
            'day' => $day
        );
        return new PhpStats_TimeInterval_Day( $timeParts, $attributes, $this->autoCompact, $this->allowUncompactedQueries );
    }
    
    protected function describeEventTypeSql()
    {
        return $this->select()
            ->from( $this->table('day_event'), 'distinct(`event_type`)' )
            ->filterByMonth($this->getTimeParts());
    }
    
    /** @todo bug (doesnt filter based on time interval) */
    /** @todo bug (doesnt filter based on event type) */
    protected function describeAttributeKeysSql( $eventType = null )
	{
		if( $this->hasBeenCompacted() )
		{
			$select = $this->describeAttributeKeysSelect('month');
		}
		else if( $this->someChildrenCompacted() )
		{
			$select = $this->describeAttributeKeysSelect('day');
		}
		else
		{
			$select = $this->describeAttributeKeysSelect();
		}
//		$select->filterByDay( $this->getTimeParts() );
//		$this->filterEventType( $this->select, $eventType);
		return $select;
	}
    
    /** @todo duplicated in day */
    function childrenAreCompacted()
	{
		foreach( $this->getDays() as $day )
		{
			if( !$day->hasBeenCompacted() )
			{
				return false;
			}
		}
		return true;
	}
	
    /** @todo duplicated in day */
    function someChildrenCompacted()
	{
		foreach( $this->getDays() as $day )
		{
			if($day->hasBeenCompacted() )
			{
				return true;
			}
		}
		return false;
	}
    
    function describeAttributeKeys( $eventType = null )
    {
        if( $this->hasBeenCompacted() || !$this->someChildrenCompacted() )
        {
             return parent::describeAttributeKeys($eventType);
        }
        
        if( isset( $this->attribKeys[$eventType] ) && !is_null( $this->attribKeys[$eventType] ) )
        {
            return $this->attribKeys[$eventType];
        }
        
        /** @todo bug (doesnt constrain by other attributes) */
        $select = $this->select()
            ->from( 'socks_day_event', array('DISTINCT( attribute_keys )') );
        $select->filterByMonth($this->getTimeParts());
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
        $this->attribKeys[$eventType] = $keys;
        return $keys;
    }
    
}