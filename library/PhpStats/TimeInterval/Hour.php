<?php
/**
* Report for a specific hour interval
* 
* @license This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Hour extends PhpStats_TimeInterval_Abstract
{
    protected $has_been_compacted;
    
    function childrenAreCompacted()
    {
        return false;
    }
    
    /** Sums up the values from the event table and caches them in the hour_event table */
    function compact()
    {
    	if( !$this->canCompact() )
    	{
			return;
    	}
    	$this->doCompactAttributes( 'hour_event' );
        $this->markAsCompacted();
    }
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension */
    function describeAttributesValues( $eventType = null )
    {
        return parent::describeAttributesValuesHour($eventType);
    }
    
    function describeSingleAttributeValues( $attribute, $eventType = null )
    {
        if( isset($this->attribValues[$eventType][$attribute]) && !is_null($this->attribValues[$eventType][$attribute]))
        {
            return $this->attribValues[$eventType][$attribute];
        }
        
        $select = $this->describeSingleAttributeValuesSql( $attribute, $eventType );
        
        $this->attribValues[$eventType][$attribute] = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $this->attribValues[$eventType][$attribute], $row[0] );
        }
        return $this->attribValues[$eventType][$attribute];
    }
    
    /** @return integer cached value forced read from cache table */
    function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        $attributes = count($attributes) ? $attributes : $this->getAttributes();
        
        $select = $this->select()
            ->from( $this->table('hour_event'), 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType )
            ->where( '`unique` = ?', $unique ? 1 : 0 )
            ->filterByHour( $this->getTimeParts() );
        $this->addCompactedAttributesToSelect( $select, $attributes, 'hour' );
        $count = (int)$select->query()->fetchColumn();
        return $count;
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    function getUncompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        $attributes = count( $attributes ) ? $attributes : $this->getAttributes();
        if( $this->isInFuture() )
        {
            return 0;
        }
        if( !$this->allowUncompactedQueries )
        {
            return 0;
        }
        
        $select = $this->select()
            ->from( $this->table('event'), $unique ? 'count(DISTINCT(`host`))' : 'count(*)' )
            ->filterByEventType( $eventType )
            ->filterByHour( $this->getTimeParts() );
        $this->addUncompactedAttributesToSelect( $select, $attributes );
        return $select->query()->fetchColumn();
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
            ->filterByHour( $this->getTimeParts() );
        $result = (bool) $select->query()->fetchColumn();
        $this->has_been_compacted = $result;
        return $result;
    }
    
    /** @return string label for this time interval (example 1am, 3pm) */
    function hourLabel()
    {
        $hour = $this->timeParts['hour'];
        if( $hour > 12 )
        {
            return $hour - 12 . 'pm';
        }
        if( 0 == $hour )
        {
			return '12am';
        }
        else if( 12 == $hour )
        {
			return '12pm';
        }
        return $hour . 'am';
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
        if( $now->toString( Zend_Date::HOUR ) > $this->timeParts['hour'] )
        {
            return true;
        }
        return false;
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
        if( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] && $now->toString( Zend_Date::MONTH ) == $this->timeParts['month'] && $now->toString( Zend_Date::DAY ) > $this->timeParts['day'] )
        {
            return false;
        }
        if( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] && $now->toString( Zend_Date::MONTH ) == $this->timeParts['month'] && $now->toString( Zend_Date::DAY ) == $this->timeParts['day'] && $now->toString( Zend_Date::HOUR ) >= $this->timeParts['hour'] )
        {
            return false;
        }
        return true;
    }
    
    function isInPresent()
    {
		$now = new Zend_Date();
		return( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] &&
			$now->toString( Zend_Date::MONTH ) == $this->timeParts['month']  &&
			$now->toString( Zend_Date::DAY ) == $this->timeParts['day'] &&
			$now->toString( Zend_Date::HOUR ) == $this->timeParts['hour']
		);
    }
    
    protected function shouldCompact()
    {
        return $this->isInPast() && !$this->hasBeenCompacted();
    }
    
    protected function describeEventTypeSql()
    {
        if( !$this->hasBeenCompacted() )
        {
	        return $this->select()->from( $this->table('event'), 'distinct(`event_type`)' )
	            ->filterByHour( $this->getTimeParts() );
		}
        
		return $this->select()->from( $this->table('hour_event'), 'distinct(`event_type`)' )
	        ->filterByHour( $this->getTimeParts() );
    }
    
    protected function describeAttributeKeysSql( $eventType = null )
    {
    	$select = $this->select();
        if( $this->hasBeenCompacted() )
        {
            $select->from( $this->table('hour_event_attributes'), 'distinct(`key`)' );
            $this->joinEventTableToAttributeSelect( $select, 'hour' );
        }
        else
        {
            $select->from( $this->table('event_attributes'), 'distinct(`key`)' );
            $this->joinEventTableToAttributeSelect($select);
        }
        $select->filterByHour( $this->getTimeParts() )
            ->filterByEventType( $eventType );
        return $select;
    }
    
    protected function setTimeParts( $timeParts )
    {
        if( !isset( $timeParts['year'] ) )
        {
            throw new PhpStats_TimeInterval_Exception_MissingTime( 'Must pass year' );
        }
        if( !isset( $timeParts['month'] ) )
        {
            throw new PhpStats_TimeInterval_Exception_MissingTime( 'Must pass month' );
        }
        if( !isset( $timeParts['day'] ) )
        {
            throw new PhpStats_TimeInterval_Exception_MissingTime( 'Must pass day' );
        }
        if( !isset( $timeParts['hour'] ) )
        {
            throw new PhpStats_TimeInterval_Exception_MissingTime( 'Must pass hour' );
        }
        $this->timeParts = $timeParts;
    }
    
    function someChildrenCompacted()
	{
		return false;
	}
    
    protected function describeSingleAttributeValuesSql( $attribute, $eventType )
    {
        $select = $this->select();
        if( !$this->hasBeenCompacted() )
        {
            $select->from( $this->table('event_attributes'), 'distinct(`value`)' );
            $this->joinEventTableToAttributeSelect($select);
            $this->addUncompactedAttributesToSelect( $select, $this->getAttributes() );
        }
        else
        {
            $select->from( $this->table('hour_event_attributes'), 'distinct(`value`)' )
                ->where( '`value` IS NOT NULL' );
            $this->joinEventTableToAttributeSelect( $select, 'hour' );
            $this->addCompactedAttributesToSelect( $select, $this->getAttributes(), 'hour', false );
        }
        $select->filterByEventType( $eventType )
            ->filterByHour( $this->getTimeParts() )
            ->where( '`key` = ?', $attribute );
        return $select;
    }
}
