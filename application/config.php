<?php    
//require_once( 'bootstrap.php' );        
error_reporting( E_ALL );
ini_set( 'display_errors' , true );
if ( ! defined('APPLICATION_ENVIRONMENT') )
{
    $hostname = rtrim( `hostname` );
    
    switch ( $hostname )
    {
        default:
            define('APPLICATION_ENVIRONMENT','localhost' );
        break;
    }
}
    
switch( APPLICATION_ENVIRONMENT )
{

    case 'localhost':
        define( 'BASE_PATH', 'E:/dev/phpstats' );
        define( 'CACHE_PATH', 'C:\Temp' );  
        define( 'MYSQL_COMMAND', 'C:\wamp\bin\mysql\mysql5.1.36\bin\mysql --user=root --password=' );
        define( 'BASE_URL', 'http://phpstats.localhost' );
    break;
    
    // production!
    default:
        define( 'BASE_PATH', '/var/www/phpstats' );
        define( 'CACHE_PATH', 'C:\Temp' );
        define( 'MYSQL_COMMAND', 'C:\wamp\bin\mysql\mysql5.1.36\bin\mysql --user=ne8 --password=9sksi2' );
        define( 'BASE_URL', 'http://phpstats.localhost' );
    break;
   
}



date_default_timezone_set('America/New_York');
        

define( 'LAYOUT_PATH', BASE_PATH . '/layouts/scripts' );
define( 'LAYOUT_HELPER_PATH', BASE_PATH . '/layouts/helpers' );
define( 'APPLICATION_PATH', BASE_PATH . '/application' );
define( 'TASK_PATH', BASE_PATH . '/tasks' );
define( 'CONFIGURATION_PATH', APPLICATION_PATH . '/config' );
define( 'LIBRARY_PATH', BASE_PATH . '/library');
define( 'DB_REFACTOR_PATH', BASE_PATH . '/database');




define( 'MODULE_PATH', APPLICATION_PATH . '/Code' );



define( 'CONFIG_SUBDIR', 'config' );
define( 'CONTROLLER_SUBDIR', 'controllers' );


define( 'USER_MODULE',  MODULE_PATH . '/User' );
define( 'USER_MODULE_CONFIG_PATH',  USER_MODULE . '/'.  CONFIG_SUBDIR );
define( 'USER_MODULE_CONTROLLER_PATH',  USER_MODULE .'/'. CONTROLLER_SUBDIR );
