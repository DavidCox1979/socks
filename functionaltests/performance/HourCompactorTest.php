<?php
/** If we got 3 hits per hour for an hour, it should take less than 1 second to process that.*/
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
        $this->db()->query( 'truncate table `day_event`' );
        $this->dataForDay( self::HOUR, self::DAY, self::MONTH, self::YEAR );
    }
    
    function testCompactsForHour()
    {
        $this->setMaxRunningTime(1);
        $hour = $this->getHour();
        $hour->compact();
    }
    
    protected function getHour()
    {
        return new PhpStats_Report_Hour( array(
            'hour' => self::HOUR,
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
    }
    
    public function dataForDay( $hour, $day, $month, $year, $multiplierFactor = 3 )
    {
        $sampleData = new SampleData();
        for( $minute = 1; $minute <= 59; $minute+= 1 )
        {
            for( $second = 1; $second <= 59; $second+= 1 )
            {
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, $multiplierFactor );
            }
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}