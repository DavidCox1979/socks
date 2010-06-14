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
    
    function testCountSpecificEventType()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 3, $month->getCount('click'), 'should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'event1' );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array(), 'event2' );
        $this->logHour( $this->dayPlusTwoDaysTimeParts(), array(), 'event3' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 3, $month->getCount(), 'should aggregrate clicks of all event types' );
    }
    
    function testCountSpecificEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array(), 'click' );
        $this->logHour( $this->dayPlusTwoDaysTimeParts(), array(), 'click' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $month->getCount('click'), 'when children compacted, should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'event1' );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array(), 'event2' );
        $this->logHour( $this->dayPlusTwoDaysTimeParts(), array(), 'event3' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $month->getCount(), 'when children compacted, should aggregrate clicks of all event types' );
    }
    
    function testCountDisableUncompacted()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $month->getCount( 'click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
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
        $this->assertEquals( 0, $month->getCount( 'click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
    }
    
    function testPassesNoAutoCompactToChildren()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $days = $month->getDays();
        $this->assertFalse( $days[1]->autoCompact(), 'when auto compact is disabled, children intervals should clone that setting' );
    }
    
    function testCountIsRepeatable()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $month = $this->getMonth();
        $this->assertEquals( 3, $month->getCount('click') );
        
        $month = $this->getMonth();
        $this->assertEquals( 3, $month->getCount('click'), 'get count should be repeatable' );
    }
    
    function testUncompactedUniques()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        
        $month = $this->getMonth();
        $this->assertEquals( 2, $month->getCount('click', array(), true ), 'should count number of unique ip addresses' );
    }
    
    function testUniquesCountedOncePerHour()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        
        $month = $this->getMonth();
        $this->assertEquals( 2, $month->getCount('click', array(), true ), 'uniques should be counted once per hour' );
    }
    
    function testUniquesWithAttributesCountedOnce()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.1' );
        
        $month = $this->getMonth();
        $this->assertEquals( 1, $month->getCount('click', array(), true ), 'uniques should be counted once per hour' );
    }
    
    function testDoesNotCompactIfIsNotInPast()
    {
        $time = new Zend_Date();
        
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        $this->logHourDeprecated( 1, $day, $month, $year, self::COUNT );
        
        $timeParts = array(
            'year' => $year,
            'month' => $month
        );
        $month = new PhpStats_TimeInterval_Month($timeParts);
        $month->getCount('click');
        
        $this->assertFalse( $month->hasBeenCompacted(), 'if is not in past, should not compact' );
    }
    
    function testShouldOmitHitsFromDifferentYear()
    {
        $this->logHour($this->getTimeParts());
        $this->insertHitDifferentYear();
        $month = $this->getMonth();
        $this->assertEquals( 1, $month->getCount('click'), 'should not count records with different year' );
    }
    
    function testShouldOmitHitsFromDifferentMonth()
    {
        $this->logHour($this->getTimeParts());
        $this->insertHitDifferentMonth();
        $month = $this->getMonth();
        $this->assertEquals( 1, $month->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $month= new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $month->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testChildrenCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $month->getCount('click'), 'getCount should not include hits of a different type in it\'s summation (when children compacted)' );
    }
    
    function testUncompactedCountNoAutoCompact()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 1, $month->getCount('click'), 'when auto-compact is disabled, should get count still' );
    }
    
    function testUncompactedCountNoAutoCompactUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.2' );

        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 2, $month->getCount('click', array(), true ), 'when auto-compact is disabled, should get [unique] count still' );
    }
    
    function testUncompactedAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1 );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 1, $month->getUncompactedCount('click'), 'should count records where attribute = 1' );
    }
    
    function testUncompactedAttributeNonAutoCompactMode()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1 );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $month->getUncompactedCount('click',array()), 'should count events with an attribute in "non auto compact" mode' );
    }
    
    function testAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'should count records where attribute = 1' );
    }

    function testGetDays()
    {
        $this->logHour( $this->getTimeParts() );
        $days = $this->getMonth()->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'should return an array of day intervals' );
    }
    
    function testDaysAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'children days should be filtered by same attributes we specified for the month' );
    }
    
    function testCountAttributesThruConstructor()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $month->getCount('click'), 'should return count only for the requested attribute (passed to constructor)' );
    }
    
    function testCountAttributesThruMethod()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 1, $month->getCount('click', array( 'a' => 1 ) ), 'should return count only for the requested attribute (passed to method)' );
    }
    
    function testAfterMonthIsCompactedChildrenDaysShouldHaveSameAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $month = $this->getMonth();
        $month->compact();
        
        $this->assertEquals( 2, $month->getCount('click') );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'a' => 1 ) );
        
        $days = $month->getDays();
        $this->assertEquals( 1, $days[1]->getCount('click'), 'after month is compacted, children days should have the same attributes as the month' );
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
    
    protected function getMonth()
    {
        return new PhpStats_TimeInterval_Month( $this->getTimeParts() );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }

    protected function insertHitDifferentMonth()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH+1, self::DAY, self::YEAR  );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }

}