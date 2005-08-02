<?php
class PhpStats_Report_DayTest extends PhpStats_ReportTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testGetHours1()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = new PhpStats_Report_Day( $this->getTimeParts() );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records for hour 1' );
    }
    
    function testGetHours2()
    {
        $this->logHour( 2, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = new PhpStats_Report_Day( $this->getTimeParts() );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[2]->getCount('clicks'), 'should count records for hour 2' );
    }
        
    function testAttribute1()
    {
        $attributes = array( 'a' => 1 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_Report_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records where attribute = 1' );
    }
    
    function testAttribute2()
    {
        $attributes = array( 'a' => 2 );
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, $attributes );
        $day = new PhpStats_Report_Day( $this->getTimeParts(), $attributes );
        $hours = $day->getHours( 'click' );
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records where attribute = 2' );
    }
    
    function testIterativelyCompactHours()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $report = $this->getReport();
        $hours = $report->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks') );
        
        $report = $this->getReport();
        $report->compact();
        
        $this->db()->query('truncate table `event`');
        
        $report = $this->getReport();
        $hours = $report->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks') );
    }    
    
    function testCompacts()
    {
        return $this->markTestIncomplete();
        $this->fail();
    }
    
    protected function getReport()
    {
        return new PhpStats_Report_Day( array(
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
    
}