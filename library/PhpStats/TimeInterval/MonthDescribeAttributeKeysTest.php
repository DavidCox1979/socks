<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_MonthDescribeAttributeKeysTest extends PhpStats_TimeInterval_TestCase
{
	const DAY = 1;
    const MONTH = 1;
    const YEAR = 2005;
    
    const COUNT = 2;
    
    function testWhenCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );

        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts() );
        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys in use' );
    }
    
    function testWhenUncompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );

        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );

        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys (uncompacted)' );
    }
    
    function testWhenChildrenCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false );
        $month->compactChildren();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys (children compacted)' );
    }
    
    function testWhenSomeChildrenCompacted()
    {
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 1 ), 'EventA' );
        $this->logHourDeprecated( 1, self::DAY, self::MONTH, self::YEAR, self::COUNT, array( 'a' => 2 ), 'EventA' );
        $day = new PhpStats_TimeInterval_Day( $this->getTimeParts(), array(), false );
        $day->compact();
        
        $month = new PhpStats_TimeInterval_Month( $this->getTimeParts(), array(), false, false );
        $this->assertEquals( array('a'), $month->describeAttributeKeys(), 'returns array of distinct attribute keys (some children compacted)' );
    }
    
    function testDescribeAttributeKeysExcludesDifferentTimeIntervalsCompacted()
    {
        return $this->markTestIncomplete();
    }
    
    function testDescribeAttributeKeysExcludesDifferentTimeIntervalsUncompacted()
    {
        return $this->markTestIncomplete();
    }
    
}
