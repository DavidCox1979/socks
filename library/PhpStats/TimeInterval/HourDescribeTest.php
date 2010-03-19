<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourDescribeTest extends PhpStats_TimeInterval_HourTestCase
{
    function testDescribeAttributeKeys()
    {
        $this->logHour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ), self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeKeysOmitsDifferentTimes()
    {
        $timeParts = array(
            'hour' => self::HOUR+1,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'a' => 1 ) );
        
        $timeParts = array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'b' => 1 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testDescribeAttributeKeysOmitsDifferentTimesCompacted()
    {
        $timeParts = array(
            'hour' => self::HOUR+1,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'a' => 1 ) );
        
        $timeParts = array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'b' => 1 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR+1,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
        $hour->compact();
        
        $hour = new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
        $hour->compact();
        
        $this->clearUncompactedEvents();
        $this->assertEquals( array('b'), $hour->describeAttributeKeys(), 'returns array of distinct attribute keys in use (compacted)' );
    }

    function testFillsInNullAttributes()
    {
        $timeParts = array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'a' => 1, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array( 'a' => 1 ) );
        $this->assertEquals( array( 'a'=>1, 'b'=>null ), $hour->getAttributes() );
    }
    
    function testFillsInNullAttributesCompacted()
    {
        $timeParts = array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        );
        $this->logHour( $timeParts, 1, array( 'a' => 1, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $timeParts, array( 'a' => 1 ) );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( array( 'a'=>1, 'b'=>null ), $hour->getAttributes() );
    }

    function testDescribeAttributeValues()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $hour->describeAttributesValues(), 'returns array of distinct keys & values for attributes in use' );
    }
    
    function testDoDescribeAttributeValues()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'b' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 2 ), $hour->doGetAttributeValues('b'), 'describes attribute values for a single attribute' );
    }
    
    function testDescribeEventTypes()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'eventA' );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'eventB' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( array( 'eventA', 'eventB' ), $hour->describeEventTypes(), 'returns array of distinct event types in use' );
    }
    
}