<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourCompactTest extends PhpStats_TimeInterval_HourTestCase
{
    function testShouldCountAllTraffic()
    {
        $this->logHour( $this->getTimeParts() );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( 1, $hour->getCount('click'), 'when hour is compacted, should include all traffic in count' );
    }
    
    function testAttributesThruConstructor()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 1, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testAttributesThruMethod()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( 1, $hour->getCount('click',array( 'a' => 1 )), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    function testAttributesNone()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 3 ) );
        $this->assertEquals( 0, $hour->getCount('click') );
    }
    
    function testNullMeansAll()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => null ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'passing null for an attribute is the same as not passing it' );
    }
    
    function testAttributes2()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 2 );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'click', 3 );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( 3, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
    }
    
    /** @todo should be it's own test case class maybe? One assertion per test method? */
    function testAttributes3()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
                
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 2 ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 1 ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'b' => 2 ) );
        $this->assertEquals( 2, $hour->getCount('click'), 'getCompactedCount should return count only for the requested attribute' );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->assertEquals( 1, $hour->getCount('click'), 'getCompactedCount should return count only for the [multiple] requested attributes' );
    }
    
    function testNullValueForAttributeMeansAll()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1, 'b' => null ) );
        $this->assertEquals( 1, $hour->getCompactedCount('click'), 'passing null for an attribute finds all records (ignores that attribute in uncompacted count)' );
    }
    
    function testUniques()
    {
    	$this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.1' );
    	$this->logHour( $this->getTimeParts(), array(), 'click', 1, '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( 2, $hour->getCount( 'click', array(), true ), 'counts unique hits after compaction' );
    }
    
    function testNonUniquesProperly()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ), 'click', 1, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ), 'click', 1, '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 2, $hour->getCount( 'click', array(), false ), 'counts non-unique hits after compaction' );
    }
    
    function testSumsUpValues()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 2, $hour->getCount('click'), 'compacting the hour should sum the values (because they are partitioned by their attributes)' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedHitsDisabledCannotCompact()
    {
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
        $hour->compact();
    }
    
    function testCompactedCountDoesntCountDifferentType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 0, $hour->getCompactedCount('click'), 'getCount should not include hits of a different type in it\'s summation' );
    }
    
    function testCompactedCountsSameType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'foo' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 1, $hour->getCompactedCount('foo'), 'getCount should include hits of a same type' );
    }
    
    function testWhenInPast_ShouldCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInPast' )
        	->will( $this->returnValue(true) );
        $hour->compact();
        $this->assertTrue( $hour->hasBeenCompacted(), 'when in past, should compact' );
    }
    
    function testWhenInPresent_ShouldNotCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInPresent' )
        	->will( $this->returnValue(true) );
        $hour->compact();
        $this->assertFalse( $hour->hasBeenCompacted(), 'when is in present, should not be able to compact' );
    }
    
    function testWhenInFuture_ShouldNotCompact()
    {
        $hour = $this->getMock('PhpStats_TimeInterval_Hour', array('isInPast','isInFuture','isInPresent'), array( $this->getTimeParts() ) );
        $hour->expects( $this->any() )
        	->method( 'isInFuture' )
        	->will( $this->returnValue(true) );
        $hour->compact();
        $this->assertFalse( $hour->hasBeenCompacted(), 'when is in future, should not be able to compact' );
    }
    
    /**
    * @expectedException Exception
    */
    function testCompactingWhenFilteringWithAttributesNotAllowed()
    {
		 $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ));
		 $hour->compact();
    }    
}