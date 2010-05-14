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
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 3, $month->getCount('click') );
    }
    
    function testCountAttributesThruConstructor()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compact();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 1, $month->getCount('click') );
    }
    
    function testCountAttributesThruParamater()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 1, $month->getCount('click', array( 'a' => 1 ) ) );
    }
    
    function testCountDisableUncompacted()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $month->getCount( 'click') );
    }
    
    function testCountDisableUncompacted2()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $timeParts = $this->getTimeParts();
        $timeParts['day'] = self::DAY;
        $day = new PhpStats_TimeInterval_Day( $timeParts, array() );
        $day->compact();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $month->getCount( 'click') );
    }
    
    function testDays()
    {
        $this->logHour( $this->getTimeParts() );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'should return an array of day intervals' );
    }
    
    function testDaysAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click') );
    }
    
    function testMonthLabel()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 'January', $month->monthLabel() );
    }
    
    function testYearLabel()      
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( '2005', $month->yearLabel() );
    }
    
    function testDescribeEventTypes()
    {
        $this->logHour( $this->getTimeParts(), array(), 'EventA' );
        $this->logHour( $this->getTimeParts(), array(), 'EventB' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array( 'EventA', 'EventB' ), $month->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testCountUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $month->getCount('click') );
	}
	
    function testCountChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $month->getCount('click') );
	}
	
	function testCountUncompactedShouldNotCompactDays()
	{
		$this->logHour( $this->getTimeParts(), array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $days = $month->getDays();
        $this->assertEquals( 0, $days[1]->getCompactedCount('click'), 'when month is in "non auto compact" mode, it\'s days should not compact' );
    }
    
    function testCanIterateDaysInAutoCompactMode()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), true );
        $this->assertNotEquals( 0, $month->getCount('click') );
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'when in auto compact mode, should be able to iterate a month\'s days and getCount() on them.' );
    }
    
    function testDescribeEventTypesExcludesDifferentTimeIntervals()
    {
        return $this->markTestIncomplete();
    }
    
    function testTimeParts()
    {
		$month = new PhpStats_TimeInterval_Month( array( 'day'=>1, 'month'=>1, 'year'=>2002 ) );
		$this->assertEquals( array( 'month'=>1, 'year'=>2002 ), $month->getTimeParts(), 'should return relevant time parts only' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedHitsDisabledCannotCompact()
    {
		return $this->markTestIncomplete();
		$month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $month->compact();
    }

}