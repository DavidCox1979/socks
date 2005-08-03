<?php
/** Report for a specific hour interval */
class PhpStats_TimeInterval_Hour extends PhpStats_TimeInterval_Abstract
{
    /** Sums up the values from the event table and caches them in the hour_event table */
    public function compact()
    {
        $attributeValues = $this->getAttributesValues();
        if( !count( $attributeValues ) )
        {
            return $this->doCompact();
        }
        foreach( $attributeValues as $attribute => $values )
        {
            foreach( $values as $value )
            {
                $this->doCompactAttribute( $attribute, $value );    
            }
        }
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'hour_event', 'count' )
            ;//->where( 'event_type = ?', $eventType );
        $this->filterByHour();
        $this->addCompactedAttributesToSelect( $this->attributes );
        return $this->select->query()->fetchColumn();
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    public function getUncompactedCount( $eventType, $attributes = array() )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' )
            ->where( 'event_type = ?', $eventType );
        $this->addUncompactedHourToSelect( $this->timeParts['hour'] );
        $this->addUncompactedAttributesToSelect( $attributes );
        $count = $this->select->query()->fetchColumn();
        return $count;
    }
    
    /** @return array of the distinct attribute keys used for this time interval */
    public function getAttributes()
    {
        $select = $this->db()->select()->from( 'event_attributes', 'distinct(`key`)' );
        $attributes = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $attributes, $row[0] );
        }
        return $attributes;
    }
    
    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension */
    public function getAttributesValues()
    {
        $attributes = $this->getAttributes();
        $return = array();
        foreach( $attributes as $attribute )
        {
            $return[ $attribute ] = $this->doGetAttributeValues( $attribute );
        }
        return $return;        
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
    
    protected function doCompact( )
    {
        $count = $this->getUncompactedCount('click');
        $bind = $this->getTimeParts();
        $bind['event_type'] = 'click';
        $bind['count'] = $count;
        $this->db()->insert( 'hour_event', $bind );
    }
    
    protected function doCompactAttribute( $attribute, $value )
    {
        $count = $this->getUncompactedCount('click', array( $attribute => $value ) );
        
        $bind = $this->getTimeParts();
        $bind['event_type'] = 'click';
        $bind['count'] = $count;
        $this->db()->insert( 'hour_event', $bind );
        
        $bind = array(
            'event_id' => $this->db()->lastInsertId(),
            'key' => $attribute,
            'value' => $value
        );
        $this->db()->insert( 'hour_event_attributes', $bind );
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
        $this->select->where( 'event.id IN (' . (string)$this->getFilterByAttributesSubquery( $attributes, 'event_attributes' ) . ')' );
    }
    
    protected function addCompactedAttributesToSelect( $attributes )
    {
        if( !count( $attributes ) )
        {
            return;
        }
        $this->select->where( 'hour_event.id IN (' . (string)$this->getFilterByAttributesSubquery( $attributes, 'hour_event_attributes' ) . ')' );
    }
    
    protected function getFilterByAttributesSubquery( $attributes, $table )
    {
        $subQuery = $this->db()->select();
        $subQuery->from( $table, 'DISTINCT(event_id)' );
        foreach( $attributes as $attributeKey => $attributeValue )
        {
            $this->doFilterByAttributes( $subQuery, $attributeKey, $attributeValue );
        }
        return $subQuery;
    }
    
    protected function doFilterByAttributes( $select, $attributeKey, $attributeValue )
    {
        $select->orWhere( sprintf( '`key` = %s && `value` = %s',
            $this->db()->quote( $attributeKey ),
            $this->db()->quote( $attributeValue )
        ));
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