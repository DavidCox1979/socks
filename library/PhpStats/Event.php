<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
/**
* (covered by UnitTestCase::findEvent)
* 
* Currently must be passed a row result set from a query,
* Looks up the log event's attributes by selecting from the event_attributes table.
*/
class PhpStats_Event extends PhpStats_Abstract
{
    protected $id;
    protected $attributes;
    protected $datetime;
    protected $host;
    
    public function __construct( $row )
    {
        $this->id = $row->id;
        $this->host = $row->host;
        $this->attributes = $this->findEventAttributes( $row->id );
        $this->datetime = strtotime($row->datetime);
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    /** @return integer timestamp */
    public function getDateTime()
    {
        return $this->datetime;
    }
    
    public function getHost()
    {
        return $this->host;
    }
    
    protected function findEventAttributes( $id )
    {
        $select = $this->db()->select()
            ->from( $this->table('event_attributes'))
            ->where('event_id = ?', $id );
        $rows = $select->query( Zend_Db::FETCH_OBJ )->fetchAll();
        $attributes = array();
        foreach( $rows as $row )
        {
            $attributes[ $row->key ] = $row->value;
        }
        return $attributes;
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}