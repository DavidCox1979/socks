<?php
/** A collection of Hour intervals for a specific day */
class PhpStats_TimeInterval_Day extends PhpStats_TimeInterval_Abstract
{
    /** @return array of PhpStats_TimeInterval_Hour */
    public function getHours()
    {
        $hours = array();
        for( $hour = 1; $hour <= 23; $hour++ )
        {
            $timeParts = $this->timeParts;
            $timeParts['hour'] = $hour;
            $hours[ $hour ] = new PhpStats_TimeInterval_Hour( $timeParts, $this->attributes );
        }
        return $hours;
    }
    
    /** Compacts the day and each of it's hours */
    public function compact()
    {
        $this->compactChildren();
        return $this->doCompact( 'day_event' );

    }    
    
    /** @return integer additive value represented by summing this day's children hours */
    public function getUncompactedCount( $eventType )
    {
        $count = 0;
        foreach( $this->getHours() as $hour )
        {
            $count += $hour->getCount( $eventType, $this->attributes );
        }
        return $count;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'day_event', 'count' )
            ->where( 'event_type = ?', $eventType );
        $this->filterByDay();
        return (int)$this->select->query()->fetchColumn();
    }
    
    protected function describeEventTypeSql()
    {
        $this->select = $this->db()->select()
            ->from( 'hour_event', 'distinct(`event_type`)' );
        $this->filterByDay();    
        return $this->select;
    }
    
    protected function describeAttributeKeysSql()
    {
        $select = $this->db()->select()->from( 'hour_event_attributes', 'distinct(`key`)' );
        return $select;
    }
    
    protected function doGetAttributeValues( $attribute )
    {
        $select = $this->db()->select()
            ->from( 'hour_event_attributes', 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute );
        $values = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $values, $row[0] );
        }
        return $values;
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
    
    /** Ensures all of this day's hours intervals have been compacted */
    protected function compactChildren()
    {
        foreach( $this->getHours() as $hour )
        {
            $hour->compact();
        }
    }   
}