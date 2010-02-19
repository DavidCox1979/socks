<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
/** Report for a specific hour interval */
class PhpStats_TimeInterval_Hour extends PhpStats_TimeInterval_Abstract
{
    /** Sums up the values from the event table and caches them in the hour_event table */
    public function compact()
    {
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return;
        }
        if( $this->isInFuture() )
        {
            return;
        }
        $this->truncatePreviouslyCompacted(); 
        $this->doCompactAttributes( 'hour_event' );
        $this->markAsCompacted();
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
    public function hasBeenCompacted()
    {
        $this->select = $this->db()->select()
            ->from( $this->table('meta'), 'count(*)' );
        $this->filterByHour();
        if( $this->select->query()->fetchColumn() )
        {
            return true;
        }
        return false;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        if( count( $attributes ))
        {
            throw new Exception( 'not implemented set attribs thru constructor' );
        }
        
        $this->select = $this->db()->select()
            ->from( $this->table('hour_event'), 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType )
            ->where( '`unique` = ?', $unique ? 1 : 0 );
        $this->filterByHour();
        $this->addCompactedAttributesToSelect( $this->attributes );
        $count = (int)$this->select->query()->fetchColumn();
        return $count;
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    public function getUncompactedCount( $eventType, $attributes = array(), $unique = false )
    {
        if( $this->isInFuture() )
        {
            return 0;
        }
        $this->select = $this->db()->select();
        if( $unique )
        {
            $this->select->from( $this->table('event'), 'count(DISTINCT(`host`))' );
        }
        else
        {
            $this->select->from( $this->table('event'), 'count(*)' );
        }
        $this->select->where( 'event_type = ?', $eventType );
        
        $this->addUncompactedHourToSelect( $this->timeParts['hour'] );
        $this->addUncompactedAttributesToSelect( $attributes );
        return $this->select->query()->fetchColumn();
    }
    
    /** @return string label for this time interval (example 1am, 3pm) */
    public function hourLabel()
    {
        $hour = $this->timeParts['hour'];
        if( $hour > 12 )
        {
            return $hour - 12 . 'pm';
        }
        return $hour . 'am';
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
        if( $now->toString( Zend_Date::HOUR ) > $this->timeParts['hour'] )
        {
            return true;
        }
        return false;
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
        if( $now->toString( Zend_Date::DAY ) > $this->timeParts['day'] )
        {
            return false;
        }
        if( $now->toString( Zend_Date::HOUR ) >= $this->timeParts['hour'] )
        {
            return false;
        }
        return true;
    }
    
    protected function shouldCompact()
    {
        return $this->isInPast() && !$this->hasBeenCompacted();
    }
    
    protected function describeEventTypeSql()
    {
        $this->select = $this->db()->select()
            ->from( $this->table('event'), 'distinct(`event_type`)' );
        $this->addUncompactedHourToSelect( $this->timeParts['hour'] );
        return $this->select;
    }
    
    /** @todo bug (doesnt filter based on time interval) */
    protected function describeAttributeKeysSql()
    {
        $select = $this->db()->select()->from( $this->table('event_attributes'), 'distinct(`key`)' );
        return $select;
    }
    
    protected function doGetAttributeValues( $attribute )
    {
        $select = $this->db()->select()
            ->from( $this->table('event_attributes'), 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute );
        $values = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $values, $row[0] );
        }
        return $values;
    }
    
    /** @todo duplicated in Day::addCompactedAttributesToSelect */
    protected function addUncompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = $this->getUncompactedFilterByAttributesSubquery( $attribute, $value, $this->table('event_attributes') );
            $this->select->where( sprintf( '%s.id IN( %s )', $this->table('event'), (string)$subQuery ) );
        }
    }
    
    protected function getUncompactedFilterByAttributesSubquery( $attribute, $value, $table )
    {
        $subQuery = $this->db()->select();
        $subQuery->from( $table, 'DISTINCT(event_id)' );

        if( $table != 'event_attributes' || !is_null($value) )
        {
            $this->doFilterByAttributesUncompacted( $subQuery, $attribute, $value );
        }

        return $subQuery;
    }
    
    protected function doFilterByAttributesUncompacted( $select, $attributeKey, $attributeValue )
    {
        if( is_null( $attributeValue ) )
        {

        }
        else
        {
            $select->where( sprintf( '`key` = %s && `value` = %s',
                $this->db()->quote( $attributeKey ),
                 $this->db()->quote( $attributeValue )
            ));
        }
    }
    
    protected function addCompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = (string)$this->getFilterByAttributesSubquery( $attribute, $value, $this->table('hour_event_attributes') );
            $this->select->where( $this->table('hour_event').'.id IN (' . $subQuery . ')' );
        }
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
    
    protected function truncatePreviouslyCompacted()
    {
        $this->select = $this->db()->select()
            ->from( $this->table('hour_event'), 'id' );
        $this->filterByHour();
        
        $subQuery = sprintf( 'event_id IN (%s)', (string)$this->select );
        $this->db()->delete( $this->table('hour_event_attributes'), $subQuery );
        
        $where = $this->db()->quoteInto( 'hour = ?', $this->timeParts['hour'] );
        $where .= $this->db()->quoteInto( ' && day = ?', $this->timeParts['day'] );
        $where .= $this->db()->quoteInto( ' && month = ?', $this->timeParts['month'] );
        $where .= $this->db()->quoteInto( ' && year = ?', $this->timeParts['year'] );
        
        $this->db()->delete( $this->table('hour_event'), $where );
    }
}