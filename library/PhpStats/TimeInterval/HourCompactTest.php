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
    
    function testWhenReportingOnAllAttributes_ShouldSumAll()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 2 ) );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 2, $hour->getCount('click'), 'when reporting on all attributes, should sum all values' );
    }
    
    function testNullValueForAttributeMeansAll()
    {
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 1 ) );
        $this->logHour( $this->getTimeParts(), array( 'a' => 1, 'b' => 2 ) );
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1, 'b' => null ) );
        $this->assertEquals( 2, $hour->getCompactedCount('click'), 'passing null for an attribute finds all records (ignores that attribute in uncompacted count)' );
    }

    function testWhenIPsDiffer_ShouldIncrementUniqueCount()
    {
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.1' );
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.2' );
    	$this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.3' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        $this->assertEquals( 3, $hour->getCount( 'click', array(), true ), 'when IPs differ, should increment unique count by the # of IPs' );
    }
    
    function testWhenIPsDiffer_ShouldCountNonUniques()
    {
        $this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.1' );
        $this->logHour( $this->getTimeParts(), array(), 'click', 2, '127.0.0.2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 4, $hour->getCount( 'click', array(), false ), 'when IPs differ should count non-uniques' );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedQueriesDisabled_ShoultNotCompact()
    {
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
		$this->assertFalse( $hour->canCompact(), 'when uncompacted queries are disabled, should not compact' );
        $hour->compact();
    }
    
    function testWhenEventTypeDoNotMatch_ShouldNotCount()
    {
        $this->logHour( $this->getTimeParts(), array(), 'differentType' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 0, $hour->getCompactedCount('click'), 'when event types do not match, should not count' );
    }
    
    function testWhenEventTypeMatch_ShouldCount()
    {
        $this->logHour( $this->getTimeParts(), array(), 'foo' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->assertEquals( 1, $hour->getCompactedCount('foo'), 'when event types match, should count' );
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