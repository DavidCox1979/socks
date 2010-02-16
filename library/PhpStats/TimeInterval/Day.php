<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
/** A collection of Hour intervals for a specific day */
class PhpStats_TimeInterval_Day extends PhpStats_TimeInterval_Abstract
{
    /** @return array of PhpStats_TimeInterval_Hour */
    public function getHours( $attributes = array() )
    {
        $attributes = ( 0 == count( $attributes ) ) ? $this->attributes : $attributes;
        $hours = array();
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $timeParts = $this->timeParts;
            $timeParts['hour'] = $hour;
            $hours[ $hour ] = new PhpStats_TimeInterval_Hour( $timeParts, $attributes );
        }
        return $hours;
    }
    
    /** Compacts the day and each of it's hours */
    public function compact()
    {
        $this->compactChildren();
        $attributeValues = $this->describeAttributesValues();
        if( !count( $attributeValues ) )
        {
            return $this->doCompact( 'day_event' );
        }
        return $this->doCompactAttributes( 'day_event' );
    }    
    
    /** @return integer additive value represented by summing this day's children hours */
    public function getUncompactedCount( $eventType, $attributes = array(), $unique = false )
    {
        $count = 0;
        foreach( $this->getHours( $attributes ) as $hour )
        {
            $count += $hour->getCount( $eventType );
        }
        return $count;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType, $attributes = array(), $unique = false )
    {
        $this->select = $this->db()->select()
            ->from( $this->table('day_event'), 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType )
            ->where( '`unique` = ?', 0 );

        $this->filterByDay();
        $this->addCompactedAttributesToSelect( $this->attributes );
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
    
    /** @todo duplicated in Hour::addCompactedAttributesToSelect */
    protected function addCompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = $this->getFilterByAttributesSubquery( $attribute, $value, $this->table('day_event_attributes') );
            $this->select->where( $this->table('day_event').'.id IN (' . (string)$subQuery . ')' );
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
    protected function describeAttributeKeysSql()
    {
        $select = $this->db()->select()->from( $this->table('hour_event_attributes'), 'distinct(`key`)' );
        return $select;
    }
    
    protected function doGetAttributeValues( $attribute )
    {
        $select = $this->db()->select()
            ->from( $this->table('hour_event_attributes'), 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute );
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
        foreach( $this->getHours() as $hour )
        {
            $hour->compact();
        }
    }
    
}