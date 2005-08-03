<?php
/** Report for a specific hour interval */
class PhpStats_TimeInterval_Hour extends PhpStats_TimeInterval_Abstract
{
    /** Sums up the values from the event table and caches them in the hour_event table */
    public function compact()
    {
        $this->truncatePreviouslyCompacted(); 
        return $this->doCompactAttributes( 'hour_event' );
    }
    
    protected function truncatePreviouslyCompacted()
    {
        $this->select = $this->db()->select()
            ->from( 'hour_event', 'id' );
        $this->filterByHour();
        
        $subQuery = sprintf( 'event_id IN (%s)', (string)$this->select );
        $this->db()->delete( 'hour_event_attributes', $subQuery );
        
        $where = $this->db()->quoteInto( 'hour = ?', $this->timeParts['hour'] );
        $where .= $this->db()->quoteInto( ' && day = ?', $this->timeParts['day'] );
        $where .= $this->db()->quoteInto( ' && month = ?', $this->timeParts['month'] );
        $where .= $this->db()->quoteInto( ' && year = ?', $this->timeParts['year'] );
        
        $this->db()->delete( 'hour_event', $where );
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'hour_event', 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType );
        $this->filterByHour();
        $this->addCompactedAttributesToSelect( $this->attributes );
        $count = (int)$this->select->query()->fetchColumn();
        // if there were no attributes for this interval
        if( !$count )
        {
           $this->select = $this->db()->select()
            ->from( 'hour_event', 'SUM(`count`)' )
            ->where( 'event_type = ?', $eventType );
            $this->filterByHour(); 
            $count = (int)$this->select->query()->fetchColumn();
        }
        return $count;
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    public function getUncompactedCount( $eventType, $attributes = array() )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' )
            ->where( 'event_type = ?', $eventType );
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
    
    protected function describeEventTypeSql()
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'distinct(`event_type`)' );
        $this->addUncompactedHourToSelect( $this->timeParts['hour'] );
        return $this->select;
    }
    
    /** bug */
    protected function describeAttributeKeysSql()
    {
        $select = $this->db()->select()->from( 'event_attributes', 'distinct(`key`)' );
        return $select;
    }
    
    protected function doGetAttributeValues( $attribute )
    {
        $select = $this->db()->select()
            ->from( 'event_attributes', 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute );
        $values = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $values, $row[0] );
        }
        return $values;
    }
    
    protected function addUncompactedHourToSelect( $hour )
    {
        $this->select->where( 'YEAR(datetime) = ?', $this->timeParts['year'] );
        $this->select->where( 'MONTH(datetime) = ?', $this->timeParts['month'] );
        $this->select->where( 'DAY(datetime) = ?', $this->timeParts['day'] );
        $this->select->where( 'HOUR(datetime) = ?', $hour );
    }
    
    protected function addUncompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        foreach( $attributes as $attribute => $value )
        {
            $this->select->where( 'event.id IN (' . (string)$this->getFilterByAttributesSubquery( $attribute, $value, 'event_attributes' ) . ')' );
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
            $this->select->where( 'hour_event.id IN (' . (string)$this->getFilterByAttributesSubquery( $attribute, $value, 'hour_event_attributes' ) . ')' );
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
    
    protected function isInPast()
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
}
