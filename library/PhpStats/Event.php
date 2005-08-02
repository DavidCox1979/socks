<?php
class PhpStats_Event
{
    protected $id;
    protected $attributes;
    
    public function __construct( $row, $attributes = array() )
    {
        $this->id = $row->id;
        $this->attributes = $attributes;
    }
    
    public function getAttributes()
    {
        return $this->attributes;
    }
    
    public function getId()
    {
        return $this->id;
    }
}