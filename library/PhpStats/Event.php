<?php
class PhpStats_Event
{
    protected $id;
    protected $attributes;
    
    public function __construct( $row )
    {
        $this->id = $row->id;
        $this->attributes = $this->findEventAttributes( $row->id );
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    protected function findEventAttributes( $id )
    {
        $select = $this->db()->select()
            ->from('event_attributes')
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