<?php
class PhpStats_Select extends Zend_Db_Select
{
    
    function filterByTimeParts( $timeParts )
    {
        if( isset($timeParts['hour']) )
        {
            $this->filterByHour($timeParts);
        }
        else if( isset($timeParts['day']) )
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
            $this->addCompactedAttribute( $attribute, $value, $addNulls );
        }
        return $this;
    }
    
    function addCompactedAttribute( $attribute, $value, $addNulls = false )
    {
        if( !$addNulls && is_null($value) )
        {
            return $this;
        }
        $code = ':' . $attribute . ':' . $value . ';';
        $this->where( "attribute_values LIKE '%{$code}%'" );
        return $this;
    }
    
    function addUncompactedAttributes( $attributes )
    {
        if( !count( $attributes ) )
        {
            return $this;
        }
        foreach( $attributes as $attribute => $value )
        {
            $subQuery = $this->getUncompactedFilterByAttributesSubquery( $attribute, $value, $this->table('event_attributes') );
            $this->where( sprintf( '%s.id IN( %s )', $this->table('event'), (string)$subQuery ) );
        }
        return $this;
    }
    
    /** @return string formatted table name (prefixed with table prefix) */
    protected function table( $table )
    {
        return PhpStats_Factory::getDbAdapter()->table( $table );
    }
    
    protected function getUncompactedFilterByAttributesSubquery( $attribute, $value, $table )
    {
        $subQuery = $this->db()->select();
        $subQuery->from( $table, 'DISTINCT(event_id)' );

        if( $table != 'event_attributes' || !is_null($value) )
        {
            $this->doFilterByAttributesUncompacted( $subQuery, $attribute, $value );
        }

        return $subQuery;
    }
    
    protected function doFilterByAttributesUncompacted( $select, $attributeKey, $attributeValue )
    {
        if( !is_null( $attributeValue ) )
        {
            $select->where( sprintf( '`key` = %s && `value` = %s',
                $this->db()->quote( $attributeKey ),
                 $this->db()->quote( $attributeValue )
            ));
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}