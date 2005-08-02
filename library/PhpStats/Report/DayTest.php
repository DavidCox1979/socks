<?php
class PhpStats_Report_DayTest extends PhpStats_ReportTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function test1()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $day = new PhpStats_Report_Day( $this->getTimeParts() );
        $hours = $day->getHours();
        $this->assertEquals( self::COUNT, $hours[1]->getCount('clicks'), 'should count records for that hour' );
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