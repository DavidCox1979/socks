<?php
class Stats_SampleController extends Zend_Controller_Action
{
    function dayAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $logger = new PhpStats_Logger();
        for( $i = 1; $i <= 1000; $i++ )
        {
            $logger->log( 'click', array( 'a' => rand( 1,3) ), mktime( rand(1,23), rand(1,59), rand(1,59), 1, 1, 2010 ) );
        }
    }
    
    function monthAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $logger = new PhpStats_Logger();
        for( $i = 1; $i <= 1000; $i++ )
        {
            $logger->log( 'click', array( 'a' => rand( 1,3) ), mktime( rand(1,23), rand(1,59), rand(1,59), 1, rand(1,30), 2010 ) );
        }
    }
    
}