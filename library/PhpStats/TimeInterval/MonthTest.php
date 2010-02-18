<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthTest extends PhpStats_TimeInterval_TestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testCount()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 1, self::DAY + 1, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 1, self::DAY + 2, self::MONTH, self::YEAR, self::COUNT );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $month->getCount( 'click') );
    }
    
    function testDays()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $days = $month->getDays();
        $this->assertEquals( self::COUNT, $days[1]->getCount('click'), 'should return an array of day intervals' );
    }
    
    function testMonthLabel()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 'January', $month->monthLabel() );
    }
    
    function testDescribeEventTypes()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'EventA' );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'EventB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array( 'EventA', 'EventB' ), $month->describeEventTypes(), 'returns array of distinct event types in use' );
    }
//    
//    function testDescribeAttributeKeys()
//    {
//        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
//        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
//        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
//        $this->assertEquals( array('a'), $day->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
//    }
//    
//    function testDescribeAttributeValues()
//    {
//        $this->logThisDayWithHour( 1, array('a' => 1 ), 'eventA' );
//        $this->logThisDayWithHour( 1, array('a' => 2 ), 'eventA' );
//        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
//        $this->assertEquals( array('a' => array( 1, 2 ) ), $day->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
//    }
    
    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'year' => self::YEAR
        );
    }
}