<?php
class HourCompactorTest extends PHPUnit_Extensions_PerformanceTestCase
{
    const HOUR = 1;
    const DAY = 1;
    const MONTH = 1;
    const YEAR = 2002;
    
    function setUp()
    {
        $this->db()->query( 'truncate table `event`' );
        $this->db()->query( 'truncate table `event_attributes`' );
        $this->db()->query( 'truncate table `hour_event`' );
        $this->db()->query( 'truncate table `hour_event_attributes`' );
        $this->db()->query( 'truncate table `day_event`' );
        $this->db()->query( 'truncate table `day_event_attributes`' );
        
        $this->dataForDay( self::HOUR-1, self::DAY, self::MONTH, self::YEAR );
        $this->dataForDay( self::HOUR, self::DAY, self::MONTH, self::YEAR );
        $this->dataForDay( self::HOUR+1, self::DAY, self::MONTH, self::YEAR );
    }
    
    function testCompactsForHour()
    {
        $this->setMaxRunningTime(15);
        $hour = $this->getHour();
        $hour->compact();
    }
    
    protected function getHour()
    {
        return new PhpStats_TimeInterval_Hour( array(
            'hour' => self::HOUR,
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
            for( $second = 1; $second <= 59; $second+= 20 )
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