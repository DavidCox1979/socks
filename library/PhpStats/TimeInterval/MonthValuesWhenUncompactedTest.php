<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthValuesWhenUncompactedTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (uncompacted)' );
    }
    
    function testWhenUncompactedQueriesDisabled()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $month->describeAttributesValues(), 'when uncompacted queries are disabled; should return empty array' );
    }
    
    function testConstrainByAnotherAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'should constrain attribute values by other attributes' );
    }
    
    function testExcludesDifferentMonth()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYear()
    {
        return $this->markTestIncomplete();
    }
    
    function testShoulfFilterByEventType()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'describing attribute values for specific event type should return values only for that type');
    }
    
    protected function clearUncompactedEvents( $skipDay = false )
    {
    	if( !$skipDay )
    	{
    		$this->db()->query('truncate table `socks_day_event`');
		}
	    $this->db()->query('truncate table `socks_hour_event`');
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }

}
