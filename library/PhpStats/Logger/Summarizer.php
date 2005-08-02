<?php
class PhpStats_Logger_Summarizer
{
    public function log( $eventType, $timeParts, $attributes, $count )
    {
        $bind = array(
            'event_type_id' => 0,
            'count' => $count,
            'year' => $timeParts['year'],
            'month' => $timeParts['month'],
            'day' => $timeParts['day'],
            'hour' => $timeParts['hour']
        );
        $this->db()->insert( 'hour_event', $bind );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}