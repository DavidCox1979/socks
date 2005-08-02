<?php
require_once( dirname( __FILE__ ) . '/../application/tests/bootstrap.php' );
$factory = new Shuffler_Db_Factory();
$refactor = new Db_Refactor( $factory );
$refactor->execute( DB_REFACTOR_PATH, isset( $argv[1] ) && $argv[1] == '--reset' );