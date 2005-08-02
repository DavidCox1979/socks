<?php
class PhpStats_Logger_SummarizerTest extends PhpStats_UnitTestCase
{
    const YEAR = 2005;
    const MONTH = 5;
    const DAY = 4;
    const HOUR = 3;
    
    function testSummarizeHours()
    {
        $logger = new PhpStats_Logger_Summarizer;
        $logger->log(
            'clicks',
            array(
                'hour' => self::HOUR,
                'year' => self::YEAR,
                'month' => self::MONTH,
                'day' => self::DAY
            ),
            array(),
            4
        );
        
        $report = new PhpStats_Report_Hour( array(
            'hour' => self::HOUR,
            'year' => self::YEAR,
            'month' => self::MONTH,
            'day' => self::DAY
        ));
        $this->assertEquals( 4, $report->getCount('clicks'));
    }
}