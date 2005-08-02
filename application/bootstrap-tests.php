<?php
ini_set( 'memory_limit', '-1' );
ini_set( 'max_execution_time', '0' );
require_once( dirname( __FILE__ ) . '/config.php');  
require_once( APPLICATION_PATH . '/bootstrap.php');  

class Ne8Test extends Ne8
{
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    private function __construct() { }  
    
    protected function setupWebsite()
    { 
        $this->website = new Website();
    }
    
    public function init()
    {
        $this->setupLoaders();
        $this->setupConfig();
        
        $this->startFrontController();
        $this->startDb();
        $this->setupRoutes();

        Zend_Registry::set( 'Ne8', $this );
        return $this;
    }
    
    
    protected function setupLoaders()
    {
        parent::setupLoaders();
        set_include_path( 
            PATH_SEPARATOR . LIBRARY_PATH . '/Ne8/tests'
            . PATH_SEPARATOR . LIBRARY_PATH . '/DataShuffler/tests'
            . PATH_SEPARATOR . LIBRARY_PATH . '/DataShuffler/library'
            . PATH_SEPARATOR . get_include_path()
        );
    }
    
    public function getUser()
    {
        return new User;
    }
}
  
$bootstrap = Ne8Test::getInstance();
$bootstrap->init();

function all_files($dir)
{
    $files = Array();
    $file_tmp= glob($dir.'*',GLOB_MARK | GLOB_NOSORT);
    foreach($file_tmp as $item)
    {
        if(substr($item,-1)!=DIRECTORY_SEPARATOR)
        {
            $files[] = $item;
        }
        else
        {
            $files = array_merge($files,all_files($item));
        }
    }
    return $files;
}

function dump($var)
{
    echo '<pre>';
    print_r( $var );   
    echo '<pre>';
}