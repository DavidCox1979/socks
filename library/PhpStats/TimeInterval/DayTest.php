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
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $day->getCount('clicks'), 'should count hits of same day (different hours)' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $day->getCount('clicks'), 'should not count records with different year' );
    }
    
    function testShouldNotCountDifferentMonth()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentMonth(); // should not count this        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $day->getCount('clicks'), 'should not count records with different year' );
    }
    
    function testAttribute1()
    {
        $attributes = array( 'a' => 1 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records where attribute = 1' );
    }
    
    function testAttribute2()
    {
        $attributes = array( 'a' => 2 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records where attribute = 2' );
    }
    
    function testGetHours1()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should return an array of hour intervals' );
    }
    
    function testGetHours2()
    {
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('clicks'), 'should return an array of hour intervals' );
    }
    
    function testCompactsChildHours()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = $this->getReport();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks') );
        
        $day->compact();
        
        $this->db()->query('truncate table `event`');
        
        $day = $this->getReport();
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'compacting the day should cause it\'s hours to be first compacted' );
    }    
    
    function testCompactsHoursIntoDay()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 11, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 13, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 23, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        
        $day = $this->getReport();
        $this->assertEquals( self::COUNT * 4, $day->getCount('clicks') );
        
        $day->compact();
        
        // delete the records from the event & hour_event table to force it to read from the day_event table.
        $this->db()->query('truncate table `event`'); 
        $this->db()->query('truncate table `hour_event`');
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 4, $day->getCount('clicks'), 'compacting the day should sum up the values for it\'s children hours and compact them at the "grain" of day_event' );
    }
    
    protected function getReport()
    {
        return new PhpStats_TimeInterval_Day( array(
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
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
    
}