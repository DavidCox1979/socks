<?php
class AllTests extends PHPUnit_Framework_TestCase
{

    static $suite;
    
    static protected function addPath( $path )
    {
        foreach( all_files( $path ) as $file )
        {
            if( substr( $file, -8 ) == 'Test.php' )
            {
                self::$suite->addTestFile( $file );
            }
        }
    }
    
    public static function suite()
    {

        PHPUnit_Util_Filter::addDirectoryToWhitelist( MODULE_PATH );
        PHPUnit_Util_Filter::addDirectoryToWhitelist( LIBRARY_PATH . '/PhpStats/' );
        
        
        self::$suite = new PHPUnit_Framework_TestSuite( 'All Tests' );
 
        self::addPath( MODULE_PATH );

        self::addPath( LIBRARY_PATH . '/PhpStats/' );
 
        return self::$suite;
    }
}