<?php
class PhpStats_Report_HourTest extends PhpStats_ReportTestCase
{
    const HOUR = 1;
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 5;
    
    function test1()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_Report_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('clicks'), 'should count records for that hour' );
    }
    
    function testShouldNotCountDifferentYear()
    {
        $this->logHour( self::HOUR, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->insertHitDifferentYear(); // should not count this        
        $hour = new PhpStats_Report_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('clicks'), 'should not count records with different year' );
    }
    
    function testAttribute()
    {
        $attributes = array( 'a' => 2 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $hour = new PhpStats_Report_Hour( $this->getTimeParts(), $attributes );
        $this->assertEquals( self::COUNT, $hour->getCount('clicks'), 'counts additive values for log events with specific attribute values' );
    }
    
    function testCompact()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $hour = new PhpStats_Report_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('clicks') );
        
        $hour->compact();
        
        $this->db()->query('truncate table `event`'); // delete the records from the event table to force it to read from the hour_event table.
        
        $hour = new PhpStats_Report_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT, $hour->getCount('clicks'), 'compacts & reads values from the hour_event cache table' );
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
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), 5, 4, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
}