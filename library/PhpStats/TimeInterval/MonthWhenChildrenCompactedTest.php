<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthTest extends PhpStats_TimeInterval_TestCase
{
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testCountSpecificEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click' );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array(), 'click' );
        $this->logHour( $this->dayPlusTwoDaysTimeParts(), array(), 'click' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $month->getCount('click'), 'when children compacted, should aggregrate clicks of a specific event type' );
    }
    
    function testCountAllEventTypeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array(), 'event1' );
        $this->logHour( $this->dayPlusOneDayTimeParts(), array(), 'event2' );
        $this->logHour( $this->dayPlusTwoDaysTimeParts(), array(), 'event3' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 3, $month->getCount(), 'when children compacted, should aggregrate clicks of all event types' );
    }
    
    function testChildrenCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( 0, $month->getCount('click'), 'getCount should not include hits of a different type in it\'s summation (when children compacted)' );
    }
    
    function testCountChildrenCompacted()
    {
        $this->db()->insert( 'socks_day_event', $this->getTimeParts()+array('attribute_keys'=>'','attribute_values'=>'','event_type'=>'click','count'=>1) );
        $this->db()->insert( 'socks_meta', array('year'=>2005,'month'=>1,'day'=>1) );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertNotEquals( 0, $month->getCount('click') );
    }
    
    protected function getMonth()
    {
        return new PhpStats_TimeInterval_Month( $this->getTimeParts() );
    }
    
    protected function insertHitDifferentYear()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH, self::DAY, self::YEAR - 1 );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }

    protected function insertHitDifferentMonth()
    {
        $time = mktime( 1, $this->minute(), $this->second(), self::MONTH+1, self::DAY, self::YEAR  );
        $logger = new Phpstats_Logger();
        $logger->log( 'click', null, array(), $time );
    }

}