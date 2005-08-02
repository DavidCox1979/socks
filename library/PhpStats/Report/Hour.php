<?php
/** Report for a specific hour interval */
class PhpStats_Report_Hour extends PhpStats_Report_Abstract
{
    /**
    * Gets the number of records for this hour, event type, and attributes
    * 
    * Uses the hour_event aggregrate table if it has a value,
    * otherwise it uses count(*) queries on the event table.
    * 
    * @param string $eventType
    * @return integer additive value
    */
    public function getCount( $eventType )
    {
        $count = $this->getCompactedCount( $eventType );   
        if( !$count )
        {
            $count = $this->getUncompactedCount( $eventType );
        }
        return $count;
    }
    
    /** @return integer cached value forced read from cache table */
    public function getCompactedCount( $eventType )
    {
        $select = $this->db()->select()
            ->from( 'hour_event', 'count' )
            ->where( 'year', $this->timeParts['year'] )
            ->where( 'month', $this->timeParts['month'] )
            ->where( 'day', $this->timeParts['day'] )
            ->where( 'hour', $this->timeParts['hour'] );
        return $select->query()->fetchColumn();
    }
    
    /** @return integer additive value represented in the (uncompacted) event table */
    public function getUncompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' );
        $this->filterByHour( $this->timeParts['hour'] );
        $this->filterByAttributes();
        return $this->select->query()->fetchColumn();
    }
    
    /** Sums up the values from the event table and caches them in the hour_event table */
    public function compact()
    {
        $count = $this->getUncompactedCount('clicks');
        $bind = $this->getTimeParts();
        $bind['event_type_id'] = 0;
        $bind['count'] = $count;
        $this->db()->insert( 'hour_event', $bind );
    }
    
    protected function filterByHour( $hour )
    {
        $this->select->where( 'MONTH(datetime) = ?', $this->timeParts['month'] );
        $this->select->where( 'HOUR(datetime) = ?', $hour );
    }
    
    protected function filterByAttributes()
    {
        if( !count( $this->attributes ) )
        {
            return;
        }
        $select = $this->db()->select();
        $select->from( 'event_attributes', 'DISTINCT(event_id)' );
        foreach( $this->attributes as $attributeKey => $attributeValue )
        {
            $select->orWhere( sprintf( '`key` = %s && `value` = %s',
                $this->db()->quote( $attributeKey ),
                $this->db()->quote( $attributeValue )
            ));
        }
        $this->select->where( 'event.id IN (' . (string)$select . ')' );
    }
    
}