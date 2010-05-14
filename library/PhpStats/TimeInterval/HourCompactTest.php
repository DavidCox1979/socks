<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
class PhpStats_TimeInterval_HourCompactTest extends PhpStats_TimeInterval_HourTestCase
{
    function testShouldCompactSpecicEventType()
    {
        $this->logHour( $this->getTimeParts(), array(), 'eventA' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( 1, $hour->getCompactedCount('eventA'), 'should get compacted count for specific event type' );
    }
    
    function testShouldCompactAllEventTypes()
    {
        return $this->markTestIncomplete();
        /*
        $this->logHour( 1, array(), 'eventtype1' );
        $this->logHour( 1, array(), 'eventtype2' );
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $hour->compact();
        $this->clearUncompactedEvents();
        
        $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts() );
        $this->assertEquals( self::COUNT*2, $hour->getCompactedCount(), 'should get compacted count for all event types' );*/
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenUncomapctedQueriesDisabledShoultNotCompact()
    {
		$hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array(), false, false );
		$this->assertFalse( $hour->canCompact(), 'when uncompacted queries are disabled, should not compact' );
        $hour->compact();
    }
    
    /**
    * @expectedException Exception
    */
    function testShouldNotCompactWhenFilteringWithAttributes()
    {
		 $hour = new PhpStats_TimeInterval_Hour( $this->getTimeParts(), array( 'a' => 1 ));
		 $hour->compact();
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
}