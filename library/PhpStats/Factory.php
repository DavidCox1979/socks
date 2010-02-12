<?php
class PhpStats_Factory
{
    /** @var PhpStats_DbAdapter */
    static $adapter;
    
    /** @param PhpStats_DbAdapter */
    static function setDbAdapter( $adpater )
    {
        self::$adapter =  $adapter;
    }
    
    /** @return PhpStats_DbAdapter */
    static function getDbAdapter()
    {
        if( is_null(self::$adapter) )
        {
            self::$adapter = new PhpStats_DbAdapter();
        }
        return self::$adapter;
    }
}