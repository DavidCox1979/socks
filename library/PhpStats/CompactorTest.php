<?php
class CompactorTest extends PhpStats_UnitTestCase
{
    function testCompactHours()
    {
        return $this->markTestIncomplete();
        $this->insertSampleData();
    }
    
    protected function insertSampleData()
    {
        $logger = new Phpstats_Logger();
        for( $i = 1; $i <= 23; $i++ )
        {
            //$logger->log( 'click')
        }
    }
}