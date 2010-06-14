<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayTest extends PhpStats_TimeInterval_DayTestCase
{
    function testCountSpecificEventType()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventType()
    {
        $this->logThisDayWithHour( 2, array(), 'event1' );
        $this->logThisDayWithHour( 12, array(), 'event2' );
        $this->logThisDayWithHour( 23, array(), 'event3' );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $day->getCount(), 'should aggregrate clicks of all event types' );
    }
    
    function testCountSpecificEventTypeChildrenCompacted()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'when children compacted, should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventTypeChildrenCompacted()
    {
        $this->logThisDayWithHour( 2, array(), 'event1' );
        $this->logThisDayWithHour( 12, array(), 'event2' );
        $this->logThisDayWithHour( 23, array(), 'event3' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( self::COUNT * 3, $day->getCount(), 'when children compacted, should aggregrate clicks of all event types' );
    }
    
    function testCountDisableUncompacted()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $day->getCount('click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
    }
    
    function testCountDisableUncompacted2()
    {
        $this->logThisDayWithHour( 1 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $timeParts = $this->getTimeParts();
        $timeParts['hour']=1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $hour->compact();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( 0, $day->getCount('click'), 'when is uncompacted & uncompacted hits are disallowed, count should be zero.' );
    }
    
    function testPassesNoAutoCompactToChildren()
    {
		$day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
		$hours = $day->getHours();
		$this->assertFalse( $hours[0]->autoCompact(), 'when auto compact is disabled, children intervals should clone that setting' );
    }
    
    function testCountIsRepeatable()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 3, $day->getCount('click') );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'get count should be repeatable' );
    }
    
    function testUncompactedUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.2' );
        
        $day = $this->getDay();
        $this->assertEquals( 2, $day->getCount('click', array(), true ), 'should count number of unique ip addresses' );
    }
    
    function testUniquesCountedOncePerHour()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
        
        $anotherHour = $this->getTimeParts() + array( 'hour' => 2 );
        $this->logHour( $anotherHour, array(), 'click', 1, '127.0.0.1' );
        
        $day = $this->getDay();
        $this->assertEquals( 2, $day->getCount('click', array(), true ), 'uniques should be counted once per hour' );
    }
    
    function testUniquesWithAttributesCountedOnce()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'click', 1, '127.0.0.1' );
        
        $day = $this->getDay();
        $this->assertEquals( 1, $day->getCount('click', array(), true ), 'uniques should be counted once per hour' );
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
            'month' => $month,
            'day' => $day,
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts );

        $day->getCount('click');
        $this->assertFalse( $day->hasBeenCompacted(), 'if is not in past, should not compact' );
    }
    
    function testShouldOmitHitsFromDifferentYear()
    {
        $this->logThisDayWithHour( 1 );
        $this->insertHitDifferentYear();
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testShouldOmitHitsFromDifferentMonth()
    {
        $this->logThisDayWithHour( 1 );
        $this->insertHitDifferentMonth();
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testShouldOmitHitsFromDifferentDay()
    {
        $this->logThisDayWithHour( 1 );
        $this->insertHitDifferentDay();
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $day->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testChildrenCompactedCountDoesntCountDifferentType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compactChildren();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $day->getCount('click'), 'getCount should not include hits of a different type in it\'s summation (when children compacted)' );
    }
    
    function testUncompactedCountNoAutoCompact()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( 1, $day->getCount('click'), 'when auto-compact is disabled, should get count still' );
    }
    
    function testUncompactedCountNoAutoCompactUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.2' );

        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( 2, $day->getCount('click', array(), true ), 'when auto-compact is disabled, should get [unique] count still' );
    }
    
    function testUncompactedAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 1, $day->getUncompactedCount('click',array()), 'should count records where attribute = 1' );
    }
    
    function testUncompactedAttributeNonAutoCompactMode()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $day->getUncompactedCount('click',array()), 'should count events with an attribute in "non auto compact" mode' );
    }
    
    function testAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( 1, $hours[1]->getCount('click'), 'should count records where attribute = 1' );
    }
    
    function testGetHours()
    {
        $this->logHour( $this->getTimeParts() );
        $hours = $this->getDay()->getHours();
        $this->assertEquals( 1, $hours[1]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHoursAttribute()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( 1, $hours[1]->getCount('click'), 'children hours should be filtered by same attributes we specified for the day' );
    }
    
    function testAttributesThruConstructor()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ), false );
        $this->assertEquals( 1, $day->getCount('click'), 'should return count only for the requested attribute (passed to constructor)' );
    } 
    
    function testAttributesThruMethod()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( 1, $day->getCount('click', array( 'a' => 1 ) ), 'should return count only for the requested attribute (passed to method)' );
    }
    
    function testAfterDayIsCompactedChildrenHoursShouldHaveSameAttributes()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        
        $this->assertEquals( 2, $day->getCount('click') );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        
        $hours = $day->getHours();
        $this->assertEquals( 1, $hours[2]->getCount('click'), 'after day is compacted, children hours should have the same attributes as the day' );
    } 
    
    function testDayLabel()
    {
        $day = $this->getDay();
        $this->assertEquals( 'Saturday, January 1, 2005', $day->dayLabel() );
    }
    
    function testShortDayLabel()
    {
        $day = $this->getDay();
        $this->assertEquals( '1', $day->dayShortLabel() );
    }
    
    function testGetsAllHours()
    {
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( 24, count($hours), 'should return 24hrs in a day' );
	}
    
    function testGetHours12am()
    {
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( '12am', $hours[0]->hourLabel() );
	}
	
    function testGetHours12pm()
    {
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( '12pm', $hours[12]->hourLabel() );
	}
    
}