<?php
class PhpStats_CompactorTest_EnumeratesTest extends PhpStats_UnitTestCase
{
    function testEnumerateHourIntervalsWithinSingleDay()
    {
        $start = array('hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $end = array('hour' => 3,'day' => 1,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 3, count( $hours ));
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[2]->getTimeParts() );
    }
    
    function testEnumerateHourIntervalsOverMultipleDays()
    {
        $start = array( 'hour' => 1,'day' => 1,'month' => 1,'year' => 2002);
        $end = array('hour' => 3,'day' => 2,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( 49, count( $hours ));
        $this->assertEquals( array( 'hour' => 0, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts(), 'starts at midnight of the starting day');
        $this->assertEquals( array( 'hour' => 1, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[1]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[2]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 23, 'day' => 1, 'month' => 1, 'year' => 2002 ), $hours[23]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[24]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 1, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[25]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 2, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[26]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 3, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[27]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 23, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[47]->getTimeParts(), 'goes beyond end time to midnight of the last day' );
        $this->assertEquals( array( 'hour' => 0, 'day' => 2, 'month' => 1, 'year' => 2002 ), $hours[48]->getTimeParts(), 'goes beyond end time to midnight of the last day' );
    }
    
    function testEnumerateHourIntervalsOverMultipleMonths()
    {
        $start = array( 'hour' => 1,'day' => 30,'month' => 1,'year' => 2002);
        $end = array('hour' => 1,'day' => 4,'month' => 3,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        
        $hours = $compactor->enumerateHours( $start, $end );

        $this->assertEquals( array( 'hour' => 0, 'day' => 30, 'month' => 1, 'year' => 2002 ), $hours[0]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 31, 'month' => 1, 'year' => 2002 ), $hours[24]->getTimeParts() );
        $this->assertEquals( array( 'hour' => 0, 'day' => 1, 'month' => 2, 'year' => 2002 ), $hours[49]->getTimeParts() );
        
    }
    
    function testEnumerateHourIntervalsOverMultipleYears()
    {
        return $this->markTestIncomplete();
    }
}