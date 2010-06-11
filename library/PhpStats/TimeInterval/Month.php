<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    /** @var string name of this interval (example hour, day, month, year) */
    protected $interval = 'month';
    
    /** @var string name of this interval's child (example hour, day, month) */
    protected $interval_child = 'day';
    
    /** @var string name of this interval's parent (example day, month, year) */
    //protected $interval_parent = 'year';
    
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
    	/** @todo write tests | $attributes = count( $attributes ) ? $attributes : $this->getAttributes(); */
    	if( $this->isInFuture() )
        {
            return 0;
        }
        if( !$this->allowUncompactedQueries )
        {
            return 0;
        }
        
    	$select = $this->select();
        if( !$this->childrenAreCompacted() )
        {
            $select->from( $this->table('event'), $unique ? 'count(DISTINCT(`host`))' : 'count(*)' )
                ->filterByEventType( $eventType )
                ->filterByMonth($this->getTimeParts());
            /* @todo write test & uncoment | $this->addUncompactedAttributes( $attributes ); */
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
            ->filterByMonth($this->getTimeParts())
            ->addCompactedAttributes( $this->getAttributes(), 'month' );
		return (int)$select->query()->fetchColumn();
    }
    
    function getDays( $attributes = array() )
    {
        if( is_array( $this->days) && count($this->days) )
    	{
			return $this->days;
    	}
        $this->days = array();
        for( $day = 1; $day <= $this->numberOfDaysInMonth(); $day++ )
        {
            $this->days[ $day ] = $this->getDay( $day, $attributes );
        }
        return $this->days;
    }
    
    function numberOfDaysInMonth()
    {
        return cal_days_in_month( CAL_GREGORIAN, $this->timeParts['month'], $this->timeParts['year'] );
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
    
    protected function doHasBeenCompacted()
    {
        $select = $this->select()
            ->from( $this->table('meta'), 'count(*)' )
            ->where( '`day` IS NULL' )
            ->filterByMonth($this->getTimeParts());
        return (bool)$select->query()->fetchColumn();
    }
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension **/
    function describeAttributesValues( $eventType = null )
    {
        if( $this->hasBeenCompacted() )
        {
            return $this->doValuesCompacted( 'month', $eventType );
        }
        if( $this->someChildrenCompacted() )
        {
            return $this->doValuesCompacted( 'day', $eventType );
        }
        return $this->doValuesUncompacted( $eventType );
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
            $attributes = $this->doValuesCompacted( 'month', $eventType );
            return $attributes[$attribute];
        }
        else if( $this->someChildrenCompacted() )
        {
            $attributes = $this->doValuesCompacted( 'day', $eventType );
            return $attributes[$attribute];
        }
        
        $values = $this->doValuesUncompacted();
        return $values[$attribute];
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
    
    /** @todo bug (doesnt constrain by other attributes) */ 
    function describeAttributeKeys( $eventType = null )
    {
        if(isset($this->attribKeys[$eventType]) && count($this->attribKeys[$eventType]) )
        {
            return $this->attribKeys[$eventType];
        }
        
        if( $this->hasBeenCompacted()  )
        {
             $keys = $this->doAttributeKeys( 'month', $eventType );
        }
        else if( $this->someChildrenCompacted() )
        {
            $keys = $this->doAttributeKeys( 'day', $eventType );
        }
        else
        {
            $keys = parent::describeAttributeKeys($eventType);
        }
        
        return $this->attribKeys[$eventType] = $keys;
    }

}