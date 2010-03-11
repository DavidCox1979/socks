<?php             
require_once( dirname(__FILE__).'/config.php');

class Ne8      
{
    /** @var Ne8 Singleton instance */
    static $_instance;
    
    protected $factory;
    protected $website;
    
    /** @var Zend_Controller_Front */
    protected $frontController;
    
    protected $router;
    
    /** @var Zend_Session */
    protected $session;
    
    /** @return Ne8 Singleton instance */
    private function __construct() { }  
    
    
    /**
     * Singleton instance
     *
     * @return Ne8
     */
    public static function getInstance()
    {
        if (null === self::$_instance)
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function init()
    {
        $this->setupLoaders();
        $this->setupConfig();
        
        $this->startFrontController();
        $this->startDb();
        $this->setupRoutes();
            
        $this->setupLayout();    
        $this->setupSession();     
        $this->setupPlugins();       
        Zend_Registry::set( 'Ne8', $this );
        
        return $this;
    }
    
    public function run()
    {
        $this->init();
        $front = Zend_Controller_Front::getInstance();
        $front->returnResponse(true);
        $response = $front->dispatch();
        if ($response->isException())
        {
            $exceptions = $response->getException();
            // handle exceptions ...
            $c = count( $exceptions );
            echo '<pre>';
                echo '<html><body>'  . $c . ' exception(s) occured while dispatching the front controller.';
                foreach($exceptions as $e )
                {
                    echo '<br /><br />' . $e->getMessage() . '<br />'  . '<div align="left">Stack Trace:' . '<pre>' . $e->getTraceAsString() . '</pre></div>';  
                }
            echo '</pre>';
        }
        else
        {
            $response->sendHeaders();
            $response->outputBody();
        }
        return $this;   
    }
    
    protected function startFrontController()
    {
        $this->frontController = Zend_Controller_Front::getInstance()
            ->setParam('env', APPLICATION_ENVIRONMENT)
            ->throwExceptions( false )
            ->setRequest( new Ne8_Controller_Request() )
            ->setDispatcher( new Ne8_Controller_Dispatcher() )
            ->addModuleDirectory( MODULE_PATH )
            ->setDefaultModule( 'Website' )
            ->setRouter( new Ne8_Controller_Router() );
    }
    
    protected function setupRoutes()
    {
        $this->frontController->getRouter()->removeDefaultRoutes();
        require CONFIGURATION_PATH . '/routes.php';
    }
    
    public function getRouter()
    {
        if( !is_null( $this->router ) )
        {
            return $this->router;
        }
        if( is_null( $this->frontController ) )
        {
            throw new Exception('No front controller');
        }
        $this->router = $this->frontController->getRouter();
        return $this->router;
    }
    
    protected function addRoute( $name, Zend_Controller_Router_Route_Interface $route)
    {
        $this->getRouter()->addRoute( $name, $route );
    }
    
    /**
    * @todo comment out mysql_connect for production!
    */
    protected function startDb()
    {
        $configuration = Zend_Registry::get('config');
        $db = Zend_Db::factory( $configuration->database ); 
        //$db->getProfiler()->setEnabled(true);
        Zend_Db_Table_Abstract::setDefaultAdapter($db); 
        Zend_Registry::set('db', $db );
    }
    
    protected function setupLayout()
    {                                         
        Zend_Layout::startMvc( LAYOUT_PATH ); 
        $layoutName = 'default';
        $this->setLayout( $layoutName );
    }
    
    public function setLayout( $layoutName )
    {
        $layout = Zend_Layout::getMvcInstance();
        Zend_Registry::set( 'layout', $layoutName );
        $layout->setLayout( $layoutName );
    }
    
    protected function setupLoaders()
    {
        set_include_path(  
            PATH_SEPARATOR . LIBRARY_PATH . '/ZendFramework/library/'
            . PATH_SEPARATOR . LIBRARY_PATH . '/ZendFramework/extras/library/'
        	. PATH_SEPARATOR . LIBRARY_PATH . '/php-csv-utils/'
            . PATH_SEPARATOR . LIBRARY_PATH . '/Swift/lib/classes/'
            . PATH_SEPARATOR . LIBRARY_PATH . '/Ne8/library/'
            . PATH_SEPARATOR . LIBRARY_PATH . '/K12/library/'
            . PATH_SEPARATOR . LIBRARY_PATH . '/Vaf/'
			. PATH_SEPARATOR . LIBRARY_PATH
            . PATH_SEPARATOR . MODULE_PATH
            . PATH_SEPARATOR . APPLICATION_PATH . '/tests/Code/'
            . PATH_SEPARATOR . get_include_path() . PATH_SEPARATOR . 'Z:\dev\vafconform'
        );
        
        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('Zend_');
        $autoloader->registerNamespace('Ne8_');
        $autoloader->registerNamespace('K12_');
        $autoloader->registerNamespace('Shuffler_');
        $autoloader->setFallbackAutoloader(true);
        
    }
    
    public function getPluginLoader()
    {
        return $this->loader;    
    }

    
    protected function setupConfig()
    {
        $config = new Zend_Config_Ini( CONFIGURATION_PATH . '/database-config.ini', APPLICATION_ENVIRONMENT );
        Zend_Registry::set('config', $config );
    }
    
    protected function setupSession()
    {              
        $this->session = new Zend_Session_Namespace('ne8');
    }
    
    /** @return Zend_Session */
    public function getSession()
    {
        return $this->session;
    }
    
    protected function setupPlugins()
    {
        $error = new Zend_Controller_Plugin_ErrorHandler();
        $error->setErrorHandlerModule( 'Website' );
        $error->setErrorHandlerController( 'Error' );
        $error->setErrorHandlerAction( 'error' );
        $this->frontController->registerPlugin( $error );
    } 
    
    public function url( array $params = array(), $name = null, $reset = false )
    {
        return $this->getRouter()->assemble( $params, $name, $reset );
    }  
    
    public function skinUrl( $uri )
    {
        return $this->getRouter()->skinUrl( $uri );
    }
    
    static public function getModules()
    {
        $modules = array();
        foreach( glob( MODULE_PATH . '/*' ) as $dir )
        {
            if( is_dir( $dir ) )
            {      
                $module = basename( $dir );
                array_push( $modules, $module );
            }
        }
        return $modules;
    }   
}
