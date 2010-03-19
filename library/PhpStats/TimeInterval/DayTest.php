<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_DayTest extends PhpStats_TimeInterval_DayTestCase
{
    function testCount()
    {
        $this->logThisDayWithHour( 2 );
        $this->logThisDayWithHour( 12 );
        $this->logThisDayWithHour( 23 );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'should count hits of same day (different hours)' );
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
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        $timeParts = array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( 2, $day->getCount('click', array(), true ) );
    }
    
    function testUniquesCountedOncePerHour()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $timeParts = array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( 2, $day->getCount('click', array(), true ), 'uniques should be counted once per hour' );
    }
    
    function testUniquesWithAttributesCountedOnce()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.1' );
        $timeParts = array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $this->assertEquals( 1, $day->getCount('click', array(), true ), 'uniques should be counted once per hour' );
    }
    
    function testCompactedUniques()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        $timeParts = array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts );
        $day->compact();
        $this->assertEquals( 2, $day->getCount('click', array(), true ) );
    }

    function testCompactsNonUniquesProperly()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.2' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $day->compact();
        $this->assertEquals( self::COUNT * 2, $day->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
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
        $this->assertFalse( $day->hasBeenCompacted() );
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
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logThisDayWithHour( 1, array(), 'differentType' );
        $day = $this->getDay();
        $this->assertEquals( 0, $day->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testUncompactedCountNoAutoCompact()
    {
        $this->logThisDayWithHour( 1, array(), 'click' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $this->assertEquals( self::COUNT, $day->getCount('click') );
    }
    
    function testUncompactedCountNoAutoCompactUniques()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        $timeParts = array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $day = new PhpStats_TimeInterval_Day( $timeParts, array(), false );
        $this->assertEquals( 2, $day->getCount('click', array(), true ) );
    }
    
    function testUncompactedAttribute()
    {
        $attributes = array( 'a' => 1 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $this->assertEquals( self::COUNT, $day->getUncompactedCount('click',array()), 'should count records where attribute = 1' );
    }
    
    function testUncompactedAttributeNonAutoCompactMode()
    {
        $attributes = array( 'a' => 1 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes, false );
        $this->assertEquals( self::COUNT, $day->getUncompactedCount('click',array()), 'should count events with an attribute in "non auto compact" mode' );
    }
    
    function testAttribute1()
    {
        $attributes = array( 'a' => 1 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 1' );
    }
    
    function testAttribute2()
    {
        $attributes = array( 'a' => 2 );
        $this->logThisDayWithHour( 1, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 2' );
    }
    
    function testGetHours1()
    {
        $this->logThisDayWithHour( 1 );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHours2()
    {
        $this->logThisDayWithHour( 2 );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHoursAttribute()
    {
        $this->logThisDayWithHour( 2, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 2, array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'children hours should be filtered by same attributes we specified for the day (uncompacted)' );
    }
    
    function testAfterDayIsCompactedChildrenHoursShouldHaveSameAttributesAsTheDay()
    {
        $this->logThisDayWithHour( 2, array( 'a' => 1 ) );
        $this->logThisDayWithHour( 2, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        
        $this->assertEquals( self::COUNT + self::COUNT, $day->getCount('click') );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'after day is compacted, children hours should have the same attributes as the day' );
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
    
    function testGetHours()
    {
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( 24, count($hours), 'should return 24hrs in a day' );
    }   
}