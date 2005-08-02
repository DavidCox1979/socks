<?php
class PhpStats_Report_Hour extends PhpStats_Report_Abstract
{
    public function getCount( $eventType )
    {
        $select = $this->db()->select()
            ->from( 'hour_event', 'count' )
            ->where( 'year', $this->timeParts['year'] )
            ->where( 'month', $this->timeParts['month'] )
            ->where( 'day', $this->timeParts['day'] )
            ->where( 'hour', $this->timeParts['hour'] );
        $count = $select->query()->fetchColumn();
        if( $count )
        {
            return $count;
        }
        return $this->getUncompactedCount( $eventType );
    }
    
    public function getUncompactedCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' );
        $this->filterByHour( $this->timeParts['hour'] );
        $this->filterByAttributes();
        return $this->select->query()->fetchColumn();
    }
    
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