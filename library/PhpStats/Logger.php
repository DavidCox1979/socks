<?php
class Phpstats_Logger
{
    public function log()
    {
        $bind = array(
            'event_type_id' => 0,
            'datetime' => 0
        );
        $this->db()->insert( 'event', $bind );
    }   
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}