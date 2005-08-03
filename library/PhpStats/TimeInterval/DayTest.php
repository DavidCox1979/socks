<?php
class PhpStats_TimeInterval_DayTest extends PhpStats_TimeIntervalTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testShouldCountSameDay()
    {
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 12, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 23, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 3, $day->getCount('click'), 'should count hits of same day (different hours)' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testShouldNotCountDifferentMonth()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentMonth(); // should not count this        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'differentType' );
        $day = $this->getDay();
        $this->assertEquals( 0, $day->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testAttribute1()
    {
        $attributes = array( 'a' => 1 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 1' );
    }
    
    function testAttribute2()
    {
        $attributes = array( 'a' => 2 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should count records where attribute = 2' );
    }
    
    function testGetHours1()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHours2()
    {
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testGetHoursAttribute()
    {
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testCompactsAttributes()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        
        $this->clearUncompactedEvents();
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $day->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    } 
    
    function testCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'differentType' );
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( 0, $day->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testGetHoursAttribute2()
    {
        return $this->markTestIncomplete();
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $day = $this->getDay();
        $day->compact();
        $this->assertEquals( self::COUNT + self::COUNT, $day->getCount('click') );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array( 'a' => 1 ) );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('click'), 'should return an array of hour intervals' );
    }
    
    function testCompactsChildHours()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click') );
        
        $day->compact();
        
        $this->db()->query('truncate table `event`');
        
        $day = $this->getDay();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('click'), 'compacting the day should cause it\'s hours to be first compacted' );
    }    
    
    function testCompactsHoursIntoDay()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 11, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 13, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 23, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 4, $day->getCount('click') );
        
        $day->compact();
        
        // delete the records from the event & hour_event table to force it to read from the day_event table.
        $this->db()->query('truncate table `event`'); 
        $this->db()->query('truncate table `hour_event`');
        
        $day = $this->getDay();
        $this->assertEquals( self::COUNT * 4, $day->getCount('click'), 'compacting the day should sum up the values for it\'s children hours and compact them at the "grain" of day_event' );
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
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( $this->getTimeParts() );
    }

    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
 
    protected function insertHitDifferentMonth()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }   
    
    protected function clearUncompactedEvents()
    {
        $this->db()->query('truncate table `hour_event`'); // delete the records from the event table to force it to read from the hour_event table. 
        $this->db()->query('truncate table `event`'); // delete the records from the event table to force it to read from the hour_event table. 
    }
    
}
