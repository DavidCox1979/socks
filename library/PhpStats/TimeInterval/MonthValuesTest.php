<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthValuesTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (uncompacted)' );
    }
    
    function testWhenUncompactedAndUncompactedQueriesDisabled()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array(), $month->describeAttributesValues(), 'should return empty array when not compacted, and uncompacted queries are disabled' );
    }
    
    function testWhenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (compacted)' );
    }
    
    function testWhenChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
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
    
    function testConstrainByAnotherAttributeUnCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when uncompacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeChildrenDaysCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false, false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when children days compacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false, false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when compacted should constrain attribute values by other attributes' );
    }
    
    function testExcludesDifferentMonth()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentMonthCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentMonthChildrenCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYear()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYearCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYearChildrenCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testShoulfFilterByEventType_WhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'when Month is uncompacted, describing attribute values for specific event type should return values only for that type');
    }
    
    function testShouldFilterByEventType_WhenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array() );
        $month->compact();
        $this->clearUncompactedEvents();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array() );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'when Month is compacted, describing attribute values for specific event type should return values only for that type');
    }
    
    function testShouldFilterByEventType_WhenChildrenCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'typeA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'typeB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1 ) ), $month->describeAttributesValues( 'typeA'), 'when Month\'s children hours are compacted, describing attribute values for specific event type should return values only for that type');
    }
    
    protected function clearUncompactedEvents( $skipDay = false )
    {
    	if( !$skipDay )
    	{
    		$this->db()->query('truncate table `socks_day_event`');
		    $this->db()->query('truncate table `socks_day_event_attributes`');
		}
	    $this->db()->query('truncate table `socks_hour_event`');
	    $this->db()->query('truncate table `socks_hour_event_attributes`');
        $this->db()->query('truncate table `socks_event`');
        $this->db()->query('truncate table `socks_event_attributes`');
    }
}
