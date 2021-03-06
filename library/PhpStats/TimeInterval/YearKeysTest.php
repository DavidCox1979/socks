<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_YearKeysTest extends PhpStats_TimeInterval_TestCase
{
	const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    
    function testWhenChildrenCompacted()
    {
        //$this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->db()->insert( 'socks_month_event', array('year' => 2005,'month'=>1,'attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        
        // $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        $this->db()->insert( 'socks_month_event', array('year' => 2005,'month'=>1,'attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1) );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a'), $year->describeAttributeKeys(), 'returns array of distinct attribute keys (uncompacted)' );
    }
//    
//    function testWhenUncompactedAndUncompactedQueriesDisabled()
//    {
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );

//        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
//        $this->assertEquals( array(), $month->describeAttributeKeys(), 'should return empty array when not compacted, and uncompacted queries are disabled' );
//    }
//    
//    function testUncompactedAndUncompactedQueriesDisabledPassedToDays()
//    {
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );

//        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
//        $days = $month->getDays();
//        $this->assertEquals( array(), $days[1]->describeAttributeKeys(), 'months day children should each return empty array when not compacted, and uncompacted queries are disabled' );
//    }
//    
//    function testWhenChildrenCompacted()
//    {
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
//        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
//        $month->compactChildren();
//        
//        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
//        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys (children compacted)' );
//    }
//    
//    function testWhenSomeChildrenCompacted()
//    {
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
//        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
//        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
//        $day->compact();
//        $this->clearUncompactedEvents( true );
//        
//        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
//        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys (some children compacted)' );
//    }
//    
//    function testDescribeAttributeKeysExcludesDifferentTimeIntervalsCompacted()
//    {
//        return $this->markTestIncomplete();
//    }
//    
//    function testDescribeAttributeKeysExcludesDifferentTimeIntervalsUncompacted()
//    {
//        return $this->markTestIncomplete();
//    }
    
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