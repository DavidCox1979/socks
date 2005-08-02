<?php
class PhpStats_TimeInterval_HourTest extends PhpStats_TimeIntervalTestCase
{
    
    const HOUR = 3;
    const DAY = 3;
    const MONTH = 3;
    const YEAR = 2005;
    
    const COUNT = 5;
    
    function testUncompactedCount()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $timeParts = array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCount should sum up additive count from the event table' );
    }
    
    function testShouldNotCountDifferentDay()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentDay(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different day' );
    }
    
    function testShouldNotCountDifferentMonth()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentMonth(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different month' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'differentType' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( 0, $hour->getCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    /**
    * @expectedException PhpStats_TimeInterval_Exception_MissingTime
    */
    function testRequiresYear()
    {
        $timeParts = $this->getTimeParts();
        unset( $timeParts['year'] );
        new PhpStats_TimeInterval_Hour( $timeParts );   
    }
    
    /**
    * @expectedException PhpStats_TimeInterval_Exception_MissingTime
    */
    function testRequiresMonth()
    {
        $timeParts = $this->getTimeParts();
        unset( $timeParts['month'] );
        new PhpStats_TimeInterval_Hour( $timeParts );   
    }
    
    /**
    * @expectedException PhpStats_TimeInterval_Exception_MissingTime
    */
    function testRequiresDay()
    {
        $timeParts = $this->getTimeParts();
        unset( $timeParts['day'] );
        new PhpStats_TimeInterval_Hour( $timeParts );   
    }
    
    /**
    * @expectedException PhpStats_TimeInterval_Exception_MissingTime
    */
    function testRequiresHour()
    {
        $timeParts = $this->getTimeParts();
        unset( $timeParts['hour'] );
        new PhpStats_TimeInterval_Hour( $timeParts );   
    }
    
    function testHourLabel1()
    {
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 1;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( '1am', $hour->hourLabel() );
    }
    
    function testHourLabel2()
    {
        $timeParts = $this->getTimeParts();
        $timeParts['hour'] = 13;
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( '1pm', $hour->hourLabel() );
    }
    
    function testCountsEventsWithAttributes()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'counts events with attributes' );
    }
    
    function testAttributeKeys()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->getAttributes(), 'returns array of distinct attribute keys in use' );
    }
    
    function testAttributeValues()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->getAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testEventTypes()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'eventA' );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->distinctEventTypes(), 'returns array of distinct event types in use' );
    }
    
    function testCompactsEventsIntoHour()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click') );
        $hour->compact();
        $this->clearUncompactedEvents();    
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'compacts data about the events table into the hour_event table' );
    }
    
    function testCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'differentType' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 0, $hour->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testCompactedCountsSameType()
    {
        return $this->markTestIncomplete();
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'foo' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( self::COUNT, $hour->getCompactedCount('foo'), 'getCount should include hits of a same type in it\'s summation' );
    }
    
    function testCompactsEventsIntoHourIfHourIsInPast()
    {
        $now = new Zend_Date();
        $time = $now->sub( 1, Zend_Date::HOUR );
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        $this->logHour( $hour, $day, $month, $year, self::COUNT );
        $timeParts = array(
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => $hour
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click') );

        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'calling getCount automatically compacts the data if the hour interval is in the past' );
    } 
    
    function testDoesNotCompactIfHourIsNotInPast()
    {
        $time = new Zend_Date();
        $hour = (int)$time->toString(Zend_Date::HOUR);
        $day = (int)$time->toString(Zend_Date::DAY);
        $month = (int)$time->toString(Zend_Date::MONTH);
        $year = (int)$time->toString(Zend_Date::YEAR);
        $this->logHour( $hour, $day, $month, $year, self::COUNT );
        $timeParts = array(
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hour' => $hour
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click') );

        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts);
        $this->assertEquals( 0, $hour->getCount('click'), '' );
    } 
    
    function testCompactsAttributes()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testCompactsAttributes2()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, 3, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( 3, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testSumsUpValues()
    {
        return $this->markTestIncomplete();
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( self::COUNT + self::COUNT, $hour->getCount('click'), 'compacting the hour should sum the values (because they are partitioned by their attributes)' );
    }
    
    protected function clearUncompactedEvents()
    {
        $this->db()->query('truncate table `event`'); // delete the records from the event table to force it to read from the hour_event table. 
    }
    
    protected function getTimeParts()
    {
        return array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
    }
    
    protected function insertHitDifferentDay()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY - 1, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
    
    protected function insertHitDifferentMonth()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH - 1, self::DAY, self::YEAR );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
}