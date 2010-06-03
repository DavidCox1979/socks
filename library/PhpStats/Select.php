<?php
class PhpStats_Select extends Zend_Db_Select
{
    function filterByHour( $timeParts )
    {
        $this->filterByDay($timeParts);
        $this->where( '`hour` = ?', $timeParts['hour'] ) ;
    }
    
    function filterByDay( $timeParts )
    {
        $this->filterByMonth($timeParts);
        $this->where( '`day` = ?', $timeParts['day'] ) ;
    }
    
    function filterByMonth( $timeParts )
    {
        $this->filterByYear($timeParts);
        $this->where( '`month` = ?', $timeParts['month'] );
    }
    
    function filterByYear( $timeParts )
    {
        $this->where( '`year` = ?', $timeParts['year'] );
    }
}