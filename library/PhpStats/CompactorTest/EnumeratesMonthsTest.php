<?php
class PhpStats_CompactorTest_EnumeratesMonthsTest extends PhpStats_UnitTestCase
{
    function testEnumerateMonthWithinSingleYear()
    {
        $start = array('month' => 1,'year' => 2002);
        $end = array( 'month' => 12,'year' => 2002 );
        
        $compactor = new PhpStats_Compactor();
        $months = $compactor->enumerateMonths( $start, $end );

        $this->assertEquals( 12, count( $months ));
        $this->assertEquals( array( 'month' => 1, 'year' => 2002 ), $months[0]->getTimeParts() );
        $this->assertEquals( array(  'month' => 12, 'year' => 2002 ), $months[11]->getTimeParts() );
    }
    
    function testEnumerateMonthSpanningMultipleYears()
    {
        $start = array('month' => 12,'year' => 2002);
        $end = array( 'month' => 1,'year' => 2003 );
        
        $compactor = new PhpStats_Compactor();
        $months = $compactor->enumerateMonths( $start, $end );

        $this->assertEquals( 2, count( $months ));
        $this->assertEquals( array( 'month' => 12, 'year' => 2002 ), $months[0]->getTimeParts() );
        $this->assertEquals( array(  'month' => 1, 'year' => 2003 ), $months[1]->getTimeParts() );
    }
}