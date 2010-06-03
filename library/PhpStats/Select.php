<?php
class PhpStats_Select extends Zend_Db_Select
{
    function filterByHour( $timeParts )
    {
        $this->filterByDay($timeParts);
        $this->where( '`hour` = ?', $timeParts['hour'] ) ;
        return $this;
    }
    
    function filterByDay( $timeParts )
    {
        $this->filterByMonth($timeParts);
        $this->where( '`day` = ?', $timeParts['day'] ) ;
        return $this;
    }
    
    function filterByMonth( $timeParts )
    {
        $this->filterByYear($timeParts);
        $this->where( '`month` = ?', $timeParts['month'] );
        return $this;
    }
    
    function filterByYear( $timeParts )
    {
        $this->where( '`year` = ?', $timeParts['year'] );
        return $this;
    }
    
    function filterByEventType( $eventType )
    {
        if( !is_null( $eventType ) )
        {
            $this->where( 'event_type = ?', $eventType );
        }
        return $this;
    }
}