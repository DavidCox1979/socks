<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_YearTest extends PhpStats_TimeInterval_YearTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testCountSpecificEventType()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneMonthTimeParts() );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts() );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts() );
        $this->assertEquals( 3, $year->getCount('click'), 'should aggregrate clicks of a specific event type' );
    }
    
    /** @todo need this test for Day */
    function testWhenChildrenCompactedCountSpecificEventType()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneMonthTimeParts() );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts() );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts() );
        $year->compactChildren();
        
        $this->assertEquals( 3, $year->getCount('click'), 'should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'event1' );
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array(), 'event2' );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts(), array(), 'event3' );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts() );
        $this->assertEquals( 3, $year->getCount(), 'should aggregrate clicks of all event types' );
    }
    
    function testCountSpecificEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneMonthTimeParts() );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts() );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $year->compactChildren();
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $year->getCount('click'), 'when children compacted, should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'event1' );
        $this->logHour( $this->dayPlusOneMonthTimeParts(), array(), 'event2' );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts(), array(), 'event3' );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $year->compactChildren();
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $year->getCount(), 'when children compacted, should aggregrate clicks of all event types' );
    }
    
    function testCountDisableUncompacted()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneMonthTimeParts() );
        $this->logHour( $this->dayPlusTwoMonthsTimeParts() );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $year->getCount( 'click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
    }
    
    function testCountDisableUncompacted2()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $timeParts = $this->getTimeParts();
        $timeParts['month'] = self::MONTH;
        $month = new PhpStats_TimeInterval_Month( $timeParts, array() );
        $month->compact();
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $year->getCount( 'click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
    }
    
    function testPassesNoAutoCompactToChildren()
    {
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $months = $year->getMonths();
        $this->assertFalse( $months[1]->autoCompact(), 'when auto compact is disabled, children intervals should clone that setting' );
    }
    
    function testCountIsRepeatable()
    {
        $this->logHour( $this->getTimeParts() );
        $this->logHour( $this->dayPlusOneDayTimeParts() );
        $this->logHour( $this->dayPlusTwoDaysTimeParts() );
        
        $year = $this->getYear();
        $this->assertEquals( 3, $year->getUncompactedCount('click') );
        
        $year = $this->getYear();
        $this->assertEquals( 3, $year->getUncompactedCount('click'), 'get count should be repeatable' );
    }
    
    function testUncompactedUniques()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        
        $year = $this->getYear();
        $this->assertEquals( 2, $year->getCount('click', array(), true ), 'should count number of unique ip addresses' );
    }
    
    function testUniquesCountedOncePerHour()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, 1, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH+1, self::YEAR, 1, array(), 'click', '127.0.0.1' );
        
        $year = $this->getYear();
        $this->assertEquals( 1, $year->getCount('click', array(), true ), 'uniques should be counted once per month' );
    }
    
    function testUniquesWithAttributesCountedOnce()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.1' );
        
        $year = $this->getYear();
        $this->assertEquals( 1, $year->getCount('click', array(), true ), 'uniques should be counted once per hour' );
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
        $year = new PhpStats_TimeInterval_Year($timeParts);
        $year->getCount('click');
        
        $this->assertFalse( $year->hasBeenCompacted(), 'if is not in past, should not compact' );
    }
    
    function testShouldOmitHitsFromDifferentYear()
    {
        $this->logHour($this->getTimeParts());
        $this->insertHitDifferentYear();
        $year = $this->getYear();
        $this->assertEquals( 1, $year->getCount('click'), 'should not count records with different year' );
    }

    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $year->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testChildrenCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $year->compactChildren();
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $year->getCount('click'), 'getCount should not include hits of a different type in it\'s summation (when children compacted)' );
    }
    
    function testUncompactedCountNoAutoCompact()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 1, $year->getCount('click'), 'when auto-compact is disabled, should get count still' );
    }
    
    function testUncompactedCountNoAutoCompactUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.2' );

        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertEquals( 2, $year->getCount('click', array(), true ), 'when auto-compact is disabled, should get [unique] count still' );
    }
    
    function testUncompactedAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1 );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 1, $year->getUncompactedCount('click'), 'should count records where attribute = 1' );
    }
    
    function testUncompactedAttributeNonAutoCompactMode()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1 );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $year->getUncompactedCount('click',array()), 'should count events with an attribute in "non auto compact" mode' );
    }
    
    function testAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ), false );
        $months = $year->getMonths();
        $this->assertEquals( 1, $months[1]->getCount('click'), 'should count records where attribute = 1' );
    }

    function testGetMonths()
    {
        $this->logHour( $this->getTimeParts() );
        $months = $this->getYear()->getMonths();
        $this->assertEquals( 1, $months[1]->getCount('click'), 'should return an array of children intervals' );
    }
    
    function testMonthsAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ), false );
        $months = $year->getMonths();
        $this->assertEquals( 1, $months[1]->getCount('click'), 'children should be filtered by same attributes we specified for the month' );
    }
    
    function testCountAttributesThruConstructor()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $year->getCount('click'), 'should return count only for the requested attribute (passed to constructor)' );
    }
    
    function testCountAttributesThruMethod()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts() );
        $this->assertEquals( 1, $year->getCount('click', array( 'a' => 1 ) ), 'should return count only for the requested attribute (passed to method)' );
    }
    
    function testYearLabel()      
    {
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts() );
        $this->assertEquals( '2005', $year->yearLabel() );
    }
    
    function testCountUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $year->getCount('click') );
    }
    
    function testCountChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $year->compactChildren();
        
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $year->getCount('click') );
    }
    
    function testCountUncompactedShouldNotCompactDays()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false );
        $months = $year->getMonths();
        $this->assertEquals( 0, $months[1]->getCompactedCount('click'), 'when month is in "non auto compact" mode, it\'s days should not compact' );
    }
    
    function testCanIterateDaysInAutoCompactMode()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), true );
        $this->assertNotEquals( 0, $year->getCount('click') );
        $months = $year->getMonths();
        $this->assertEquals( 1, $months[1]->getCount('click'), 'when in auto compact mode, should be able to iterate a month\'s days and getCount() on them.' );
    }
    
    function testTimeParts()
    {
        $year = new PhpStats_TimeInterval_Year( array( 'day'=>1, 'month'=>1, 'year'=>2002 ) );
        $this->assertEquals( array( 'year'=>2002 ), $year->getTimeParts(), 'should return relevant time parts only' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedHitsDisabledCannotCompact()
    {
        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array(), false, false );
        $year->compact();
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