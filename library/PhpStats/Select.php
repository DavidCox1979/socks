<?php
class PhpStats_Select extends Zend_Db_Select
{
    
    function filterByTimeParts( $timeParts )
    {
        if( isset($timeParts['day']) )
        {
            $this->filterByDay($timeParts);
        }
        else
        {
            $this->filterByMonth($timeParts);
        }
        return $this;
    }
    
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
    
    function addCompactedAttributes( $attributes, $table = 'day', $addNulls = true )
    {
        if( !count( $attributes ) )
        {
            return $this;
        }
        
        foreach( $attributes as $attribute => $value )
        {
            if( !$addNulls && is_null($value) )
            {
                continue;
            }
            $code = ':' . $attribute . ':' . $value . ';';
            $this->where( "attribute_values LIKE '%{$code}%'");
        }
        return $this;
    }
    
    function addCompactedAttribute( $attribute, $value, $addNulls = false )
    {
        if( !$addNulls && is_null($value) )
        {
            return;
        }
        $code = ':' . $attribute . ':' . $value . ';';
        $this->where( "attribute_values LIKE '%{$code}%'" );
        return $this;
    }
    
    /** @return string formatted table name (prefixed with table prefix) */
    protected function table( $table )
    {
        return PhpStats_Factory::getDbAdapter()->table( $table );
    }
}