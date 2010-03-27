<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthDescribeAttributeValuesTest extends PhpStats_TimeInterval_TestCase
{
    function testWhenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (compacted)' );
    }
    
    function testWhenUncompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (uncompacted)' );
    }
    
    function testWhenChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (children compacted)' );
    }
    
    function testWhenSomeChildrenCompacted()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'EventA' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'EventA' );
        
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compact();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a' => array( 1, 2 ) ), $month->describeAttributesValues(), 'should return array of distinct keys & values for attributes in use (children compacted)' );
    }
    
    function testConstrainByAnotherAttributeUnCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false );

        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when uncompacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeChildrenDaysCompacted()
    {
		$this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 3, 'b' => 2 ) );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array( 'b' => 2 ), false );
        $this->assertEquals( array( 2, 3 ), $month->describeSingleAttributeValues('a'), 'when children days compacted should constrain attribute values by other attributes' );
    }
    
    function testConstrainByAnotherAttributeCompacted()
    {
		return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentMonth()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentMonthCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYear()
    {
        return $this->markTestIncomplete();
    }
    
    function testExcludesDifferentYearCompacted()
    {
        return $this->markTestIncomplete();
    }
    
}
