<?php
class Phpstats_Logger
{
    public function log( $type, $attributes = array() )
    {
        $event_id = $this->insertEvent( $type );
        $this->insertAttributes( $event_id, $attributes );
    }   
    
    /** @return the event-id that is assigned to the logged event */
    protected function insertEvent( $type )
    {
        $bind = array(
            'event_type_id' => 0,
            'datetime' => 0
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