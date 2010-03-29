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
    public function compactChildren()
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
    
    /** @todo doesnt filter by attributes, do the childrenCompacted "3 part" thing */
    public function getUncompactedCount( $eventType=null, $attributes = array(), $unique = false )
    {
    	$attributes = count( $attributes ) ? $attributes : $this->getAttributes();
    	if( !$this->allowUncompactedQueries )
    	{
			return 0;
    	}
    	$childrenAreCompacted = $this->childrenAreCompacted();
    	$this->select = $this->db()->select();
        if( !$childrenAreCompacted )
        {
            /** @todo duplicated in Hour::getUncompactedCount() */
            /** @todo duplicated in Day::getUncompactedCount() */
            if( $unique )
            {
                $this->select->from( $this->table('event'), 'count(DISTINCT(`host`))' );
            }
            else
            {
                $this->select->from( $this->table('event'), 'count(*)' );
            }
            $this->select
                ->where( 'event_type = ?', $eventType );
            $this->filterByMonth();
            /* @todo write test & uncoment */
            //$this->addUncompactedAttributesToSelect( $attributes );
        }
        else
        {
            $this->select
				->from( $this->table('day_event'), 'SUM(`count`)' )
				->where( '`unique` = ?', $unique ? 1 : 0 );
			$this->filterEventType($eventType);
			$this->filterByMonth();
			$this->addCompactedAttributesToSelect( $attributes, 'day' );
        }
        
        return (int)$this->select->query()->fetchColumn();
    }
    
    public function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
		$attribs = $this->getAttributes();
		
		$this->select = $this->db()->select()
			->from( $this->table('month_event'), 'SUM(`count`)' )
			->where( '`unique` = ?', $unique ? 1 : 0 );
			
		if( !is_null( $eventType ) )
		{
			$this->select->where( 'event_type = ?', $eventType );
		}
        if( count($attribs))
		{
			$this->addCompactedAttributesToSelect( $attribs, 'month' );
		}
		$this->filterByMonth();
		
		return (int)$this->select->query()->fetchColumn();
    }
    
    public function getDays( $attributes = array() )
    {
        if( is_array( $this->days) && count($this->days) )
    	{
			return $this->days;
    	}
        $numberOfDaysInMonth = cal_days_in_month( CAL_GREGORIAN, $this->timeParts['month'], $this->timeParts['year'] );
        $this->days = array();
        for( $day = 1; $day <= $numberOfDaysInMonth; $day++ )
        {
            $this->days[ $day ] = $this->getDay( $day, $attributes );
        }
        return $this->days;
    }
    
    public function monthLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], 1, $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::MONTH_NAME );
    }
    
    public function yearLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], 1, $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::YEAR );
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
	public function hasBeenCompacted()
	{
		if( isset($this->has_been_compacted) )
		{
			return $this->has_been_compacted;
		}
		$this->select = $this->db()->select()
			->from( $this->table('meta'), 'count(*)' )
			->where( '`day` IS NULL' );
		$this->filterByMonth();
		if( $this->select->query()->fetchColumn() )
		{
			$this->has_been_compacted = true; 
			return true;
		}
		$this->has_been_compacted = false; 
		return false;
	}
	
    /**
    * @todo duplicated in day
    * @todo doesnt filter based on time interval
    */
    public function describeSingleAttributeValues( $attribute, $eventType = null )
    {
        if( $this->hasBeenCompacted() )
        {
			$attributes = $this->getAttributes();
            $hasAttributes = $this->hasAttributes();
            
            $this->select = $this->db()->select()
                ->from( $this->table('month_event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
            
            $this->joinEventTableToAttributeSelect('month');
            $this->filterEventType( $eventType );
            
            if( $hasAttributes )
            {
            	$this->addCompactedAttributesToSelect( $attributes, 'month', false );
			}
        }
        else if( $this->someChildrenCompacted() )
        {
            $attributes = $this->getAttributes();
            $hasAttributes = $this->hasAttributes();
            
            $this->select = $this->db()->select()
                ->from( $this->table('day_event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
            
            $this->joinEventTableToAttributeSelect('day');
            $this->filterEventType( $eventType );
            
            if( $hasAttributes )
            {
            	$this->addCompactedAttributesToSelect( $attributes, 'day', false );
			}
        }
        else
        {
            $attributes = $this->getAttributes();
		
			$this->select = $this->db()->select()
                ->from( $this->table('event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
            
            $this->joinEventTableToAttributeSelect();
            $this->filterEventType( $eventType );
            
            $this->addUncompactedAttributesToSelect( $attributes );
        }
        $values = array();
        
        $rows = $this->select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            if( !is_null($row[0]) )
            {
                array_push( $values, $row[0] );
            }
        }
        return $values;
    }
    
    public function isInFuture()
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
	
	public function isInPast()
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
	
	public function isInPresent()
	{
		$now = new Zend_Date();
		return( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] &&
			$now->toString( Zend_Date::MONTH ) == $this->timeParts['month']
		);
	}

	public function getTimeParts()
	{
		$return = array();
		$return['month'] = $this->timeParts['month'];
		$return['year'] = $this->timeParts['year'];
		return $return;
	}
	
	/** @todo duplicated in day */
	protected function doCompactAttributes( $table )
	{
		$attributeKeys = $this->describeAttributeKeys();
		
		$dayEventTbl = $this->table('day_event');
		$dayEventAttributesTbl = $this->table('day_event_attributes');
		
		$cols = array(
			'count' => 'SUM(`count`)',
			'event_type',
			'unique'
		);
		$this->select = $this->db()->select()
			->from( $dayEventTbl, $cols );
		
		// join & group on each attribute we are segmenting the report by
		foreach( $attributeKeys as $attribute )
		{	
			$alias = $attribute.'TBL';
			$cond = sprintf( '%s.event_id = %s.id', $alias, $dayEventTbl );
			$cond .= sprintf( " AND %s.`key` = '%s'", $alias, $attribute );
			$this->select
				->joinLeft( array( $alias => $dayEventAttributesTbl ), $cond, array( $attribute => 'value' ) )
				->group( sprintf('%s.value',$alias) );
			
		}
		
		// "pivot" (group) on the unique column, so we get uniques and non uniques seperately
		$this->select->group( sprintf('%s.unique', $dayEventTbl ) );
		
		// also "pivot" the data on the event_type column so we get them back seperate
		$this->select->group( sprintf('%s.event_type', $dayEventTbl ) );
		
		// only return records for this month
		$this->filterByMonth();
		
		$result = $this->db()->query( $this->select )->fetchAll( Zend_Db::FETCH_OBJ );
		foreach( $result as $row )
		{
			// insert record into month_event
			$bind = $this->getTimeParts();
			$bind['event_type'] = $row->event_type;
			$bind['unique'] = $row->unique;
			$bind['count'] = $row->count;
			$this->db()->insert( $this->table('month_event'), $bind );
			
			// get the eventId
			$eventId = $this->db()->lastInsertId();
			
			// insert record(s) into month_event_attributes
			foreach( $attributeKeys as $attribute )
			{
				$bind = array(
					'event_id' => $eventId,
					'key' => $attribute,
					'value' => $row->$attribute
				);
				$attributeTable = $this->table('month_event_attributes');
				$this->db()->insert( $attributeTable, $bind );
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
        $this->select = $this->db()->select()
            ->from( $this->table('day_event'), 'distinct(`event_type`)' );
        $this->filterByMonth();    
        return $this->select;
    }
    
    /** @todo bug (doesnt filter based on time interval) */
    /** @todo bug (doesnt filter based on event type) */
    protected function describeAttributeKeysSql( $eventType = null )
	{
		if( $this->hasBeenCompacted() )
		{
			$this->describeAttributeKeysSelect('month');
		}
		else if( $this->someChildrenCompacted() )
		{
			$this->describeAttributeKeysSelect('day');
		}
		else
		{
			$this->describeAttributeKeysSelect();
		}
//		$this->filterByDay();
//		$this->filterEventType($eventType);
		return $this->select;
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
}
