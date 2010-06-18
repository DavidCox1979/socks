<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthValuesWhenChildrenCompactedTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenChildrenCompacted()
    {
        $this->db()->insert( 'socks_day_event', $this->getTimeParts() + array('attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts() + array('attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1,'day'=>1) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (children compacted)' );
    }
    
    function testWhenSomeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compact();
        $this->clearUncompactedEvents( true );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (children compacted)' );
    }
    
    function testConstrainByAnotherAttribute()
    {
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:1;:b:1;') );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:2;:b:2;') );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a,b','attribute_values'=>':a:3;:b:2;') );
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1,'day'=>1) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false, false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when children compacted should constrain attribute values by other attributes' );
    }
    
    function testExcludesDifferentMonthChildrenCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYearChildrenCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testShouldFilterByEventType_WhenChildrenCompacted()
    {
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'typeA') );
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'typeB') );
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1,'day'=>1) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'when Month\'s children hours are compacted, describing attribute values for specific event type should return values only for that type');
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
