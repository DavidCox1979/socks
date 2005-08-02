<?php
class Stats_ReportController extends Zend_Controller_Action
{
    
    function indexAction()
    {
        $this->render( 'index', null, true );
    }
    
    function dayAction()
    {
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->view->day = $day;
        $this->render( 'day', null, true );
    }
    
    function monthAction()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->view->month = $month;
        $this->render( 'month', null, true );
    }
    
    function preDispatch()
    {
        $this->startTime = $this->getMicrotime();
    }
    
    function postDispatch()
    {
        $endTime = $this->getMicrotime();
        $totalTime = $endTime - $this->startTime;
        echo 'Generated in '.$totalTime.' seconds';
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
    
    protected function getMicrotime()
    {
        list($useg,$seg)=explode(' ',microtime());
        return ((float)$useg+(float)$seg);
    }
}