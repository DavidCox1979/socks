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
    
}
