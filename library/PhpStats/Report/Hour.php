<?php
class PhpStats_Report_Hour
{
    /** @var array */
    protected $timeParts;
    
    /** @var array */
    protected $attributes;
    
    /** @var Zend_Db_Select */
    protected $select;
    
    /**
    * @param array $timeparts (hour, month, year, day )
    * @param array $attributes only records that match these
    *   attributes & values will be included in the report
    */
    public function __construct( $timeParts, $attributes = array() )
    {
        $this->timeParts = $timeParts;
        $this->attributes = $attributes;
    }
    
    public function getCount( $eventType )
    {
        $this->select = $this->db()->select()
            ->from( 'event', 'count(*)' );
        $this->filterByHour( $this->timeParts['hour'] );
        $this->filterByAttributes();
        return $this->select->query()->fetchColumn();
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
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
    
}