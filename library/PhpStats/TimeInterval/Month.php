<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_Month extends PhpStats_TimeInterval_Abstract
{
    
    public function getUncompactedCount( $eventType, $attributes = array(), $unique = false )
    {
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
            $this->addUncompactedMonthToSelect();
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
    
    public function hasBeenCompacted()
    {
        return false;
    }
    
    /**
    * @todo duplicated in day
    * @todo doesnt filter based on time interval
    */
    public function doGetAttributeValues( $attribute )
    {
        if( $this->autoCompact )
        {
            $select = $this->db()->select()
                ->from( $this->table('hour_event_attributes'), 'distinct(`value`)' )
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
    
    protected function getDay( $day )
    {
        $timeParts = array(
            'year' => $this->timeParts['year'],
            'month' => $this->timeParts['month'],
            'day' => $day
        );
        return new PhpStats_TimeInterval_Day( $timeParts, $this->getAttributes(), $this->autoCompact );
    }
    
    protected function shouldCompact()
    {
        return false;
    }
    
    /** Compacts all of this month's day intervals */
    protected function compactChildren()
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
    
    protected function describeEventTypeSql()
    {
        $this->select = $this->db()->select()
            ->from( $this->table('day_event'), 'distinct(`event_type`)' );
        $this->filterByMonth();    
        return $this->select;
    }
    
    /** @todo bug (doesnt filter based on time interval) */
    protected function describeAttributeKeysSql( $eventType = null )
    {
        $select = $this->db()->select()->from( $this->table('event_attributes'), 'distinct(`key`)' );
        return $select;
    }
}
