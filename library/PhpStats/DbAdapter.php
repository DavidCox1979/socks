<?php
class PhpStats_DbAdapter
{
    /** @var string change the table prefix string */
    protected $tablePrefix = 'socks_';
    
    /** @return string table prefix */
    function getTablePrefix()
    {
        return $this->tablePrefix;
    }
    
    /** @return string formatted table name (prefixed with table prefix) */
    function table( $table )
    {
        return $this->getTablePrefix().$table;
    }
}