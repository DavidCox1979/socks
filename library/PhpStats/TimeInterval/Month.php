<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    /** @var string name of this interval (example hour, day, month, year) */
    protected $interval = 'month';
    
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
    
    public function getUncompactedCount( $eventType=null, $attributes = array(), $unique = false )
    {
    	if( !$this->allowUncompactedQueries )
    	{
			return 0;
    	}
        if( !$this->autoCompact )
        {
            /** @todo duplicated in Hour::getUncompactedCount() */
            /** @todo duplicated in Day::getUncompactedCount() */
            $this->select = $this->db()->select();
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
            $this->addCompactedAttributesToSelect( $attributes, 'day' );
        }
        else
        {
            $count = 0;
            foreach( $this->getDays() as $day )
            {
                $count += $day->getCount( $eventType );
            }
            return $count;
        }
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
    
    public function getDays()
    {
        $days = cal_days_in_month( CAL_GREGORIAN, $this->timeParts['month'], $this->timeParts['year'] );
        $return = array();
        for( $day = 1; $day <= $days; $day++ )
        {
            $return[ $day ] = $this->getDay( $day );
        }
        return $return;
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
        if( !is_null($eventType))
        {
            throw new Exception('not implemented');
        }
        if( $this->autoCompact )
        {
            $this->select = $this->db()->select()
                ->from( $this->table('hour_event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
        }
        else
        {
            $attributes = $this->getAttributes();
			$hasAttributes = $this->hasAttributes();
		
			$this->select = $this->db()->select()
                ->from( $this->table('event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
                
            $this->joinEventTableToAttributeSelect();
                
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
	 
    protected function getDay( $day )
    {
        $timeParts = array(
            'year' => $this->timeParts['year'],
            'month' => $this->timeParts['month'],
            'day' => $day
        );
        return new PhpStats_TimeInterval_Day( $timeParts, $this->getAttributes(), $this->autoCompact, $this->allowUncompactedQueries );
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
    protected function childrenAreCompacted()
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
    protected function someChildrenCompacted()
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
