<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
/** A collection of Hour intervals for a specific day */
class PhpStats_TimeInterval_Day extends PhpStats_TimeInterval_Abstract
{
    protected $hours = array();
    
    protected $has_been_compacted; 
    
    /** @return array of PhpStats_TimeInterval_Hour */
    public function getHours( $attributes = array() )
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
            $this->hours[$attributesKey][ $hour ] = new PhpStats_TimeInterval_Hour( $timeParts, $attributes );
        }
        return $this->hours[$attributesKey];
    }
    
    /** Compacts the day and each of it's hours */
    public function compact()
    {
        if( $this->hasBeenCompacted() )
        {
            return;
        }
        if( $this->isInFuture() )
        {
            return;
        }
        if( $this->isInPresent() )
        {
            return;
        }
        if( $this->hasZeroCount() )
        {
            $this->markAsCompacted();
            return;
        }

        $this->compactChildren();
        $attributeValues = $this->describeAttributesValues();
        if( !count( $attributeValues ) )
        {
            $this->doCompact( 'day_event' );
            $this->markAsCompacted();
            return;
        }
        
        $this->doCompactAttributes( 'day_event' );
        $this->markAsCompacted();
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
    public function hasBeenCompacted()
    {
        if( !is_null($this->has_been_compacted))
        {
            return $this->has_been_compacted;
        }
        $this->select = $this->db()->select()
            ->from( $this->table('meta'), 'count(*)' )
            ->where( '`hour` IS NULL' );
        $this->filterByDay();
        if( $this->select->query()->fetchColumn() )
        {
            $this->has_been_compacted = true; 
            return true;
        }
        $this->has_been_compacted = false; 
        return false;
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
        
        if( !$this->hasBeenCompacted() )
        {
            
            // has hits in hour_event?
            $this->select = $this->db()->select()
                ->from( 'socks_hour_event', 'count(*)' );
            $this->filterByDay();
            if( 0 < $this->db()->query( $this->select )->fetchColumn() )
            {
                return false;
            }
        
        
            // has hits in event?
            $this->select = $this->db()->select()
                ->from( 'socks_event', 'count(*)' );
            $this->addUncompactedDayToSelect();
            if( 0 < $this->db()->query( $this->select )->fetchColumn() )
            {
                return false;
            }
        }
        
        // has no hits
        return true;
    }
    
    /** @return integer additive value represented by summing this day's children hours */
    public function getUncompactedCount( $eventType, $attributes = array(), $unique = false )
    {
        if( $this->isInFuture() )
        {
            return 0;
        }
        $attributes = count($attributes) ? $attributes : $this->getAttributes();
        $this->select = $this->db()->select()
            ->from( $this->table('hour_event'), 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType )
            ->where( '`unique` = ?', $unique ? 1 : 0 );
        $this->filterByDay();
        $this->addCompactedAttributesToSelect( $attributes, 'hour' );
        $count = (int)$this->select->query()->fetchColumn();
        return $count;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        $attribs = $this->getAttributes();
        $this->select = $this->db()->select()
            ->from( $this->table('day_event'), 'SUM(`count`)' )
            ->where( '`unique` = ?', $unique ? 1 : 0 );
            
        if( !is_null( $eventType ) )
        {
            $this->select->where( 'event_type = ?', $eventType );
        }

        $this->filterByDay();
        if( count($attribs))
        {
            $this->addCompactedAttributesToSelect( $attribs );
        }
        return (int)$this->select->query()->fetchColumn();
    }
    
    /** @return string label for this day (example January 1st 2005) */
    public function dayLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], $this->timeParts['day'], $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::DATE_FULL );
    }
    
    public function dayShortLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], $this->timeParts['day'], $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::DAY_SHORT );
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
        if( $now->toString( Zend_Date::DAY ) > $this->timeParts['day'] )
        {
            return true;
        }
        return false;
    }
    
    public function isInPresent()
    {
        $now = new Zend_Date();
        return( $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'] &&
            $now->toString( Zend_Date::MONTH ) == $this->timeParts['month']  &&
            $now->toString( Zend_Date::DAY ) == $this->timeParts['day']
        );
    }
    
    public function isInFuture()
    {
        $now = new Zend_Date();
        if( $now->toString( Zend_Date::YEAR ) > $this->timeParts['year'] )
        {
            return false;
        }
        if( $now->toString( Zend_Date::MONTH ) > $this->timeParts['month'] )
        {
            return false;
        }
        if( $now->toString( Zend_Date::DAY ) >= $this->timeParts['day'] )
        {
            return false;
        }
        return true;
    }
    
    /** @todo duplicated in Hour::addCompactedAttributesToSelect */
    protected function addCompactedAttributesToSelect( $attributes, $table = 'day' )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = $this->getFilterByAttributesSubquery( $attribute, $value, $this->table( $table.'_event_attributes') );
            $this->select->where( $this->table($table.'_event').'.id IN (' . (string)$subQuery . ')' );
        }
    }
    
    protected function describeEventTypeSql()
    {
        $this->select = $this->db()->select()
            ->from( $this->table('hour_event'), 'distinct(`event_type`)' );
        $this->filterByDay();    
        return $this->select;
    }
    
    /** @todo bug (doesnt filter based on time interval) */
    protected function describeAttributeKeysSql( $eventType = null )
    {
        if( $this->hasBeenCompacted() )
        {
            $select = $this->db()->select()->from( $this->table('day_event_attributes'), 'distinct(`key`)' );
        }
        else
        {
            $select = $this->db()->select()
                ->from( $this->table('hour_event_attributes'), 'distinct(`key`)' )
                ->where( 'value IS NOT NULL');
            if(!is_null($eventType))
            {
                $select->where( 'event_id in ( select id from socks_hour_event where event_type = ? )', $eventType );
            }
        }
        return $select;
    }
    
    /**
    * @todo duplicated in month 
    * @todo if child hours have been compacted hit the hours table
    */
    protected function doGetAttributeValues( $attribute )
    {
        if( $this->hasBeenCompacted() )
        {
            $select = $this->db()->select()
                ->from( $this->table('day_event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
        }
        else
        {
            $select = $this->db()->select()
                ->from( $this->table('event_attributes'), 'distinct(`value`)' )
                ->where( '`key` = ?', $attribute );
        }
        $values = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            if( !is_null($row[0]) )
            {
                array_push( $values, $row[0] );
            }
        }
        return $values;
    }
    
    /** Ensures all of this day's hours intervals have been compacted */
    protected function compactChildren()
    {
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return;
        }
        foreach( $this->getHours() as $hour )
        {
            if( !$hour->isInPast() || !$hour->hasBeenCompacted() )
            {
                $hour->compact();
            }
        }
    }
    
}
