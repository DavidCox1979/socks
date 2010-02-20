<?php
class MonthCompactorTest extends PHPUnit_Extensions_PerformanceTestCase
{

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
        
        $this->dataForDay( 1, 1, self::MONTH, self::YEAR );
        $this->dataForDay( 1, 15, self::MONTH, self::YEAR );
        $this->dataForDay( 1, 20, self::MONTH, self::YEAR );
    }
    
    function testCompactsForMonth()
    {
        $this->setMaxRunningTime(5);
        $month = $this->getMonth();

        foreach( $month->getDays() as $day )
        {
            $day->getCount('click');
            $day->getCount('click');
        }
    }
    
    protected function getMonth()
    {
        return new PhpStats_TimeInterval_Month( array(
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
    }
    
    public function dataForDay( $hour, $day, $month, $year, $multiplierFactor = 2 )
    {
        $sampleData = new SampleData();
        $attribs = array( 'attribute1' => rand( 1,5), 'attribute2' => rand( 1,5) );
        $sampleData->logHit( $hour, 1, 1, $day, $month, $year, $multiplierFactor, $attribs, 'click' );
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}