<?php
class Stats_ReportController extends Zend_Controller_Action
{
    function dayAction()
    {
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->view->day = $day;
        $this->render( 'day', null, true );
    }
    
    function sampleAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $logger = new PhpStats_Logger();
        for( $i = 1; $i <= 10000; $i++ )
        {
            $logger->log( 'click', array( 'a' => rand( 1,3) ), mktime( rand(1,23), rand(1,59), rand(1,59), 1, 1, 2010 ) );
        }
    }
    
    protected function getTimeParts()
    {
        return array(
            'year' => $this->_getParam('year'),
            'month' => $this->_getParam('month'),
            'day' => $this->_getParam('day'),
            'hour' => $this->_getParam('hour')
        );
    }
}