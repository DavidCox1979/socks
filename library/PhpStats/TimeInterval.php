<?php
/**
* This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
/** Reports are "partitioned" by their time intervals & custom attributes */
interface PhpStats_TimeInterval
{
    
    /**
    * Gets the number of records for this time interval, event type, and attributes
    * 
    * Uses highest abstracted aggregrate table if available,
    * otherwise it uses next lowest grain of aggregate table or the even table
    * 
    * @param string $eventType
    * @return integer additive value
    */
    function getCount( $eventType = null );
}