<?php
class PhpStats_CompactorTest_EnumeratesDaysTest extends PhpStats_UnitTestCase
{
    function testEnumerateDayIntervalsWithinSingleMonth()
    {
        $start = array('day' => 1,'month' => 1,'year' => 2002);
        $end = array('day' => 3,'month' => 1,'year' => 2002);
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 3, count( $days ));
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $days[1]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 1, 'year' => 2002 ), $days[2]->getTimeParts() );
    }
    
    function testEnumerateDayIntervalsSpanningMultipleMonths()
    {
        $start = array('day' => 1,'month' => 1,'year' => 2002);
        $end = array( 'day' => 3, 'month' => 3,'year' => 2002 );
        
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 62, count( $days ));
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 2, 'month' => 1, 'year' => 2002 ), $days[1]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 1, 'year' => 2002 ), $days[2]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 2, 'year' => 2002 ), $days[33]->getTimeParts() );
        $this->assertEquals( array( 'day' => 3, 'month' => 3, 'year' => 2002 ), $days[61]->getTimeParts() );
    }
    
    function testEnumerateDayIntervalSpanningMultipleYears()
    {
        $start = array('day' => 31,'month' => 12,'year' => 2002);
        $end = array( 'day' => 1, 'month' => 1,'year' => 2003 );
        
        $compactor = new PhpStats_Compactor();
        $days = $compactor->enumerateDays( $start, $end );

        $this->assertEquals( 2, count( $days ));
        $this->assertEquals( array( 'day' => 31, 'month' => 12, 'year' => 2002 ), $days[0]->getTimeParts() );
        $this->assertEquals( array( 'day' => 1, 'month' => 1, 'year' => 2003 ), $days[1]->getTimeParts() );
    }
}