<?php
require_once( dirname( __FILE__ ) . '/../application/bootstrap.php' );
foreach( glob( 'E:\dev\phpstats\application\database\*.sql' ) as $file )
{
    exec( MYSQL_COMMAND . ' phpstats<' . $file );
    echo $file . "\n";
}