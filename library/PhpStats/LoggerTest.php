<?php
class LoggerTest extends PhpStats_UnitTestCase
{
    function testLog()
    {
        return $this->markTestIncomplete();
        $logger = new PhpStats_Logger;
        $event = $this->getEvent();
        $logger->log( 'click', array(
            'foo1' => 'bar1',
            'foo2' => 'bar2'
        ));
    }
    
    
}