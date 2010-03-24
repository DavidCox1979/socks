<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourTest extends PhpStats_TimeInterval_HourTestCase
{    
    function testHasNoAttributes()
    {
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array() );
		$this->assertFalse( $hour->hasAttributes(), 'when has no attributes hasAttributes() should return false' );
    }
    
    function testHasAttributes()
    {
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
		$this->assertTrue( $hour->hasAttributes(), 'when has attributes hasAttributes() should return true' );
    }
    
    function testUncompactedCount()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $timeParts = array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCount should sum up additive count from the event table' );
    }
    
    function testUncompactedCountDisabled()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $timeParts = array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array(), false, false );
        $this->assertEquals( 0, $hour->getCount('click'), 'when uncompacted count is disabled and has not been compacted, getCount() should return 0' );
    }
    
    function testUncompactedCount2()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $timeParts = array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCount should sum up additive count from the event table' );
    }
    
    function testUncompactedUniques()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        $timeParts = array(
            'hour' => self::HOUR,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        );
        $hour = new PhpStats_TimeInterval_Hour( $timeParts );
        $this->assertEquals( 2, $hour->getCount('click', array(), true ) );
    }
    
    function testShouldNotCountDifferentDay()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentDay(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different day' );
    }
    
    function testShouldNotCountDifferentMonth()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentMonth(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different month' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'should not count records with different year' );
    }
    
    function testUncompactedCountDoesntCountDifferentType()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'differentType' );
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
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ), false );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'counts events with attributes' );
    }

}