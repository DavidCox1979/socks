<?php
/** Records log events for later reporting */
class PhpStats_Logger
{
    /**
    * Record a log event
    * 
    * @param string $eventType type of event this is (ex. click, search_impressions)
    * @param array $attributes optional array of custom fields to be used in reporting later, defaults to empty array
    * @param integer $dateTime an optional unix timestamp of the date this log event should be backdated to, defaults to now
    */
    public function log( $eventType, $attributes = array(), $dateTime = null )
    {
        $event_id = $this->insertEvent( $eventType, $dateTime );
        $this->insertAttributes( $event_id, $attributes );
    }   
    
    /** @return the event-id that is assigned to the logged event */
    protected function insertEvent( $eventType, $dateTime )
    {
        $dateTime = new Zend_Date( $dateTime );
        $bind = array(
            'event_type' => $eventType,
            'datetime' => $dateTime->toString( Zend_Date::ISO_8601 )
        );
        $this->db()->insert( 'event', $bind );
        return $this->db()->lastInsertId();
    }
    
    protected function insertAttributes( $event_id, $attributes )
    {
        foreach( $attributes as $attributeKey => $attributeValue )
        {
            $bind = array(
                'event_id' => $event_id,
                'key' => $attributeKey,
                'value' => $attributeValue
            );
            $this->db()->insert( 'event_attributes', $bind );
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}