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
    
    /** Sums up the values from the event table and caches them in the hour_event table */
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

        $this->doCompactAttributes( 'hour_event' );
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
            ->from( $this->table('meta'), 'count(*)' );
        $this->filterByHour();
        if( $this->select->query()->fetchColumn() )
        {
            $this->has_been_compacted = true;
            return true;
        }
        $this->has_been_compacted = false;
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
        $this->addCompactedAttributesToSelect( $this->getAttributes() );
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
        /** @todo duplicated in Day::getUncompactedCount() */
        /** @todo duplicated in Month::getUncompactedCount() */
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
    
    public function doGetAttributeValues( $attribute )
    {
        if( isset($this->attribValues[$attribute]) && !is_null($this->attribValues[$attribute]))
        {
            return $this->attribValues[$attribute];
        }
        $select = $this->db()->select()
            ->from( $this->table('event_attributes'), 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute );
        $this->attribValues[$attribute] = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        $this->attribValues[$attribute] = array();
        foreach( $rows as $row )
        {
            array_push( $this->attribValues[$attribute], $row[0] );
        }
        return $this->attribValues[$attribute];
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
    protected function describeAttributeKeysSql( $eventType = null )
    {
        $select = $this->db()->select()->from( $this->table('event_attributes'), 'distinct(`key`)' );
        return $select;
    }

    /** @todo get rid of this and use the paramaterized method on the super class */
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
    
}
