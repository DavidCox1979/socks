<?php
class PhpStats_CompactorTest_LockTest extends PhpStats_UnitTestCase
{
    function testAcquiresLock()
    {
        $compactor = new PhpStats_Compactor();
        $this->assertFalse( $compactor->hasLock() );
        $compactor->acquireLock();
        $this->assertTrue( $compactor->hasLock() );
    }
    
    function testAcquiresLockAndBlocksConcurrentCompacters()
    {
        $compactor1 = new PhpStats_Compactor();
        $compactor2 = new PhpStats_Compactor();
        $compactor1->acquireLock();
        $this->assertFalse( $compactor2->hasLock() );
    }
    
    function testFreesLock()
    {
        $compactor = new PhpStats_Compactor();
        $compactor->acquireLock();
        $compactor->freeLock();
        $this->assertFalse( $compactor->hasLock() );
    }
    
    /**
    * @expectedException Exception
    */
    function testWhenCantAcquireLockThrowsException()
    {
        $compactor1 = new PhpStats_Compactor();
        $compactor2 = new PhpStats_Compactor();
        $compactor1->acquireLock();
        $compactor2->acquireLock();
    }
    
    function testCompactingWithoutLockShouldAcquireLock()
    {
        return $this->markTestIncomplete();
    }
}
