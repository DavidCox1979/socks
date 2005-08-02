<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthTest extends PhpStats_TimeIntervalTestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testCount()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 1, self::DAY + 1, self::MONTH, self::YEAR, self::COUNT );
        $this->logHour( 1, self::DAY + 2, self::MONTH, self::YEAR, self::COUNT );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( self::COUNT * 3, $month->getCount( 'click') );
    }
    
    function testDays()
    {
        $this->logHour( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $days = $month->getDays();
        $this->assertEquals( self::COUNT, $days[1]->getCount('click'), 'should return an array of day intervals' );
    }
    
    function testMonthLabel()
    {
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( 'January', $month->monthLabel() );
    }
    
    protected function getTimeParts()
    {
        return array(
            'month' => self::MONTH,
            'year' => self::YEAR
        );
    }
}