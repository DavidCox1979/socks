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
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHourDeprecated( 1, self::DAY + 1, self::MONTH, self::YEAR, self::COUNT );
        $this->logHourDeprecated( 1, self::DAY + 2, self::MONTH, self::YEAR, self::COUNT );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $month->getCount( 'click') );
    }
    
    function testDays()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $days = $month->getDays();
        $this->assertEquals( self::COUNT, $days[1]->getCount('click'), 'should return an array of day intervals' );
    }
    
    function testDaysAttributes()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $days = $month->getDays();
        $this->assertEquals( self::COUNT, $days[1]->getCount('click') );
    }
    
    function testMonthLabel()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 'January', $month->monthLabel() );
    }
    
    function testDescribeEventTypes()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'EventB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array( 'EventA', 'EventB' ), $month->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testDescribeAttributeKeys()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );

        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeValues()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testMonthIsRepeatable()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $first_count = $month->getCount( 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $second_count = $month->getCount( 'EventA' );
        
        $this->assertEquals( $first_count, $second_count, 'calling describeAttributeValues() multiple times will not re-compact the data.' );
    }
    
    function testMonthCanReadFromEventTableWithoutCompactingDays()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $month->getCount('click') );
        $days = $month->getDays();
        $this->assertEquals( 0, $days[1]->getCompactedCount('click'), 'when month is in "non auto compact" mode, it\'s days should not compact' );
    }
    
    function testDescribeEventTypesExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeKeysExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }
    
    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'year' => self::YEAR
        );
    }
}