<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_YearValuesTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenChildrenCompacted()
    {
        //$this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->db()->insert( 'socks_month_event', array('year' => 2005,'month'=>1,'attribute_keys'=>'a','attribute_values'=>':a:1;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        
        // $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        $this->db()->insert( 'socks_month_event', array('year' => 2005,'month'=>1,'attribute_keys'=>'a','attribute_values'=>':a:2;','event_type'=>'EventA','unique'=>0,'count'=>1) );
        
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1) );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $year->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (uncompacted)' );
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
