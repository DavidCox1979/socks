<?php
/**
* Records log events for later reporting
* 
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_Logger extends PhpStats_Abstract
{
    /**
    * Record a log event
    * 
    * @param string $eventType type of event this is (ex. click, search_impressions)
    * @param string $hostname IP address to associate with event
    * @param array $attributes optional array of custom fields to be used in reporting later, defaults to empty array
    * @param integer $dateTime an optional unix timestamp of the date this log event should be backdated to, defaults to now
    */
    public function log( $eventType, $hostname = null, $attributes = array(), $dateTime = null )
    {
        $event_id = $this->insertEvent( $eventType, $hostname, $dateTime );
        $this->insertAttributes( $event_id, $attributes );
    }   
    
    /** @return the event-id that is assigned to the logged event */
    protected function insertEvent( $eventType, $hostname, $dateTime )
    {
        $dateTime = new Zend_Date( $dateTime );
        $bind = array(
            'event_type' => $eventType,
            'host' => $hostname,
            'datetime' => $dateTime->toString( Zend_Date::ISO_8601 )
        );
        $this->db()->insert( $this->table('event'), $bind );
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
            $this->db()->insert( $this->table('event_attributes'), $bind );
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}