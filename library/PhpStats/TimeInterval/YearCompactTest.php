<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_YearCompactTest extends PhpStats_TimeInterval_YearTestCase
{
    
    function testAfterYearIsCompactedChildrenShouldHaveSameAttributes()
    {
        return $this->markTestIncomplete();
        //$this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
//        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
//        
//        $year = $this->getYear();
//        $year->compact();
//        
//        $this->assertEquals( 2, $year->getCount('click') );
//        
//        $year = new PhpStats_TimeInterval_Year( $this->getTimeParts(), array( 'a' => 1 ) );
//        
//        $months = $year->getDays();
//        $this->assertEquals( 1, $months[1]->getCount('click'), 'after is compacted, children should have the same attributes' );
    }
    
}