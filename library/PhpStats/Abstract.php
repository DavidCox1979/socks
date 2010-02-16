<?php
abstract class PhpStats_Abstract
{
    /** @return string formatted table name (prefixed with table prefix) */
    protected function table( $table )
    {
        return PhpStats_Factory::getDbAdapter()->table( $table );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return PhpStats_Factory::getDbAdapter();
    }
}