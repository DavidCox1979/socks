<?php
class PhpStatas_ReportTest_ReportHoursTest extends PhpStats_ReportTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    function testReportHours()
    {
        $this->insertDataHours( self::DAY, self::MONTH, self::YEAR );
        $report = new PhpStats_Report( array(
            'hour' => 1,
            'month' => self::MONTH,
            'day' => self::DAY,
            'year' => self::YEAR
        ));
        $this->assertEquals( self::EVENTS_PER_HOUR, $report->getCount('clicks') );
    }
    
}