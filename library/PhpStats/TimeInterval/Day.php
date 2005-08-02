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
    
    /**
    * Ensures all of this day's hours intervals have been compacted
    * sums each of this day's hour's additive values and caches them in the day_event table
    **/
    public function compact()
    {
        $this->compactChildren();
        $bind = $this->getTimeParts();
        //$bind['event_type_id'] = 0;
        $bind['count'] = $this->getCount('clicks');
        $this->db()->insert( 'day_event', $bind );
    }    
    
    /** @return integer additive value represented by summing this day's children hours */
    public function getUncompactedCount( $eventType )
    {
        $count = 0;
        foreach( $this->getHours() as $hour )
        {
            $count += $hour->getCount( $eventType );
        }
        return $count;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'day_event', 'count' );
        $this->filterByDay();
            
        return $this->select->query()->fetchColumn();
    }
    
    /** @return string label for this day (example January 1st 2005) */
    public function dayLabel()
    {
        $time = mktime( 1, 1, 1, $this->timeParts['month'], $this->timeParts['day'], $this->timeParts['year'] );
        $date = new Zend_Date( $time );
        return $date->toString( Zend_Date::DATE_FULL );
    }
    
    protected function compactChildren()
    {
        foreach( $this->getHours() as $hour )
        {
            $hour->compact();
        }
    }
    
}
