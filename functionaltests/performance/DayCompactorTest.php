<?php
class DayCompactorTest extends PHPUnit_Extensions_PerformanceTestCase
{
    
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2002;
    
    function setUp()
    {
        $this->db()->query( 'truncate table `socks_event`' );
        $this->db()->query( 'truncate table `socks_event_attributes`' );
        $this->db()->query( 'truncate table `socks_hour_event`' );
        $this->db()->query( 'truncate table `socks_hour_event_attributes`' );
        $this->db()->query( 'truncate table `socks_day_event`' );
        $this->db()->query( 'truncate table `socks_day_event_attributes`' );
        $this->db()->query( 'truncate table `socks_meta`' );
        
        $this->dataForDay( 1, self::DAY, self::MONTH, self::YEAR );
        $this->dataForDay( 2, self::DAY, self::MONTH, self::YEAR );
        $this->dataForDay( 3, self::DAY, self::MONTH, self::YEAR );
    }
    
    function testCompactsForDay()
    {
        $this->setMaxRunningTime(5);
        $day = $this->getDay();
        $day->compact();
    }
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
    }
    
    public function dataForDay( $hour, $day, $month, $year, $multiplierFactor = 2 )
    {
        $sampleData = new SampleData();
        for( $minute = 1; $minute <= 59; $minute+= 10 )
        {
            for( $second = 1; $second <= 59; $second+= 30 )
            {
                $attribs = array( 'attribute1' => rand( 1,5), 'attribute2' => rand( 1,5), 'attribute3' => rand(1,10) );
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, $multiplierFactor, $attribs, 'click' );
            }
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}