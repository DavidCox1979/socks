<?php
/** Report for a specific hour interval */
class PhpStats_TimeInterval_Hour extends PhpStats_TimeInterval_Abstract
{
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'hour_event', 'count' );
        $this->filterByHour();
        return $this->select->query()->fetchColumn();
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    public function getUncompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' )
            ->where( 'event_type = ?', $eventType );
        $this->addUncompactedHourToSelect( $this->timeParts['hour'] );
        $this->addUncompactedAttributesToSelect();
        return $this->select->query()->fetchColumn();
    }
    
    /** Sums up the values from the event table and caches them in the hour_event table */
    public function compact()
    {
        $count = $this->getUncompactedCount('click');
        $bind = $this->getTimeParts();
        $bind['event_type'] = 'click';
        $bind['count'] = $count;
        $this->db()->insert( 'hour_event', $bind );
    }
    
    public function getAttributes()
    {
        $select = $this->db()->select()
            ->from( 'event_attributes', 'distinct(`key`)' );
        $attributes = array();
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $attributes, $row[0] );
        }
        return $attributes;
    }
    
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
    
    protected function addUncompactedAttributesToSelect()
    {
        if( !count( $this->attributes ) )
        {
            return;
        }
        $this->select->where( 'event.id IN (' . (string)$this->getFilterByAttributesSubquery() . ')' );
    }
    
    protected function getFilterByAttributesSubquery()
    {
        $subQuery = $this->db()->select();
        $subQuery->from( 'event_attributes', 'DISTINCT(event_id)' );
        foreach( $this->attributes as $attributeKey => $attributeValue )
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
    
}