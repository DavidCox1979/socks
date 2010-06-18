<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthValuesWhenCompactedTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenCompacted()
    {
        $timeParts = $this->getTimeParts();
        unset($timeParts['day']);
        $this->db()->insert( 'socks_month_event', $timeParts+array('attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'EventA') );
        $this->db()->insert( 'socks_month_event', $timeParts+array('attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'EventA') );
        
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use' );
    }
    
    function testConstrainByAnotherAttribute()
    {
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:1;:b:1;') );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:2;:b:2;') );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:3;:b:2;') );
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1,'day'=>1) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false, false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'should constrain attribute values by other attributes' );
    }
    
    function testExcludesDifferentMonthCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYearCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testShouldFilterByEventType()
    {
        $timeParts = $this->getTimeParts();
        unset($timeParts['day']);
        $this->db()->insert( 'socks_month_event', $timeParts+array('attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'typeA') );
        $this->db()->insert( 'socks_month_event', $timeParts+array('attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'typeB') );
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array() );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'when Month is compacted, describing attribute values for specific event type should return values only for that type');
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
