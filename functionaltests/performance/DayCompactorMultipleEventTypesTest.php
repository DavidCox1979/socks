<?php
class DayCompactorMultipleEventTypesTest extends PHPUnit_Extensions_PerformanceTestCase
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
        
        $this->dataForDay( 1, self::DAY, self::MONTH, self::YEAR, 'eventA' );
        $this->dataForDay( 2, self::DAY, self::MONTH, self::YEAR, 'eventB' );
        $this->dataForDay( 2, self::DAY, self::MONTH, self::YEAR, 'eventA' );
        $this->dataForDay( 2, self::DAY, self::MONTH, self::YEAR, 'eventB' );
        $this->dataForDay( 3, self::DAY, self::MONTH, self::YEAR, 'eventA' );
        $this->dataForDay( 3, self::DAY, self::MONTH, self::YEAR, 'eventC' );
    }
    
    function testCompactsForDay()
    {
        $profiler = Zend_Registry::get('db')->getProfiler();
        $profiler->clear();
        $profiler->setEnabled(true);
        
        $this->setMaxRunningTime(15);
        $day = $this->getDay();
        $day->compact();
        
//        $totalTime    = $profiler->getTotalElapsedSecs();
//$queryCount   = $profiler->getTotalNumQueries();
//$longestTime  = 0;
//$longestQuery = null;
//foreach ($profiler->getQueryProfiles() as $query) {
//    echo $query->getQuery() . "\n\n";
//  if ($query->getElapsedSecs() > $longestTime) {
//      $longestTime  = $query->getElapsedSecs();
//      $longestQuery = $query->getQuery();
//  }
//}
//echo "\n\n";
//echo 'Executed ' . $queryCount . ' queries in ' . $totalTime .
//   ' seconds' . "\n";
//echo 'Average query length: ' . $totalTime / $queryCount .
//   ' seconds' . "\n";
//echo 'Queries per second: ' . $queryCount / $totalTime . "\n";
//echo 'Longest query length: ' . $longestTime . "\n";
//echo "Longest query: \n" . $longestQuery . "\n";
        $profiler->setEnabled(false);
    }
    
    protected function getDay()
    {
        return new PhpStats_TimeInterval_Day( array(
            'day' => self::DAY,
            'month' => self::MONTH,
            'year' => self::YEAR
        ));
    }
    
    public function dataForDay( $hour, $day, $month, $year, $eventType = 'click' )
    {
        $sampleData = new SampleData();
        for( $minute = 1; $minute <= 59; $minute+= 10 )
        {
            for( $second = 1; $second <= 59; $second+= 30 )
            {
                $attribs = array( 'attribute1' => rand( 1,5), 'attribute2' => rand( 1,5) );
                $sampleData->logHit( $hour, $minute, $second, $day, $month, $year, 1, $attribs, $eventType);
            }
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }   
}