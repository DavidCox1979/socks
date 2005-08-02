<?php
class PhpStats_Report_HourTest extends PhpStats_UnitTestCase
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
    
    protected function logHour( $hour, $day, $month, $year, $times )
    {
        for( $repeat = 1; $repeat <= $times; $repeat++ )
        {
            $time = mktime( $hour, $this->minute(), $this->second(), $day, $month, $year );
            $logger = new Phpstats_Logger();
            $logger->log( 'click', array(), $time );
        }
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( self::HOUR, $this->minute(), $this->second(), 5, 4, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', array(), $time );
    }
    
    protected function minute()
    {
        return rand(1,59);
    }
    
    protected function second()
    {
        return rand(1,59);
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
}