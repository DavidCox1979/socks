<?php
abstract class PhpStats_Abstract
{
    /** @return string formatted table name (prefixed with table prefix) */
    protected function table( $table )
    {
        return PhpStats_Factory::getDbAdapter()->table( $table );
    }
}