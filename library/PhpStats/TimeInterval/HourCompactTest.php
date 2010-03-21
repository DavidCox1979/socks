<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourCompactTest extends PhpStats_TimeInterval_HourTestCase
{
    function testCompacts()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click') );
        $hour->compact();
        $this->clearUncompactedEvents();    
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'compacts data about the events table into the hour_event table' );
    }
    
    function testCompactsAttributes()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testCompactsAttributes0()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 3 ) );
        $hour->compact();
        $this->assertEquals( 0, $hour->getCount('click') );
    }
    
    function testNullMeansAll()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => null ) );
        $this->assertEquals( self::COUNT * 2, $hour->getCount('click'), 'passing null for an attribute is the same as not passing it' );
    }
    
    function testCompactsAttributes2()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, 3, array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( 3, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testCompactsAttributes3()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1, 'b' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1, 'b' => 2 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2, 'b' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( self::COUNT * 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
                
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( self::COUNT * 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 1 ) );
        $this->assertEquals( self::COUNT * 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 2 ) );
        $this->assertEquals( self::COUNT * 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->assertEquals( self::COUNT, $hour->getCount('click'), 'getCompactedCount should return count only for the [multiple] requested attributes' );
    }
    
    function testCompactedCountNullAttributeMeansAll()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1, 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1, 'b' => null ) );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( self::COUNT, $hour->getCompactedCount('click'), 'passing null for an attribute finds all records (ignores that attribute in uncompacted count)' );
    }
    
    function testCompactsUniques()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.1' );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array(), 'click', '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( 2, $hour->getCount( 'click', array(), true ), 'counts unique hits after compaction' );
    }
    
    function testCompactsNonUniquesProperly()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'click', '127.0.0.1' );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'click', '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( self::COUNT * 2, $hour->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
    }
    
    function testSumsUpValues()
    {
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ) );
        $this->logHourDeprecated( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( self::COUNT + self::COUNT, $hour->getCount('click'), 'compacting the hour should sum the values (because they are partitioned by their attributes)' );
    }
    
}