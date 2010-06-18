<?php
class PhpStats_TimeInterval_Year extends PhpStats_TimeInterval_Abstract
{
    /** @var array of PhpStats_TimeInterval_Day children */
    protected $months;
    
    /** @var string name of this interval (example hour, day, month, year) */
    protected $interval = 'year';

    /** @return array multi-dimensional array of distinct attributes, and their distinct values as the 2nd dimension **/
    function describeAttributesValues( $eventType = null )
    {
        if( $this->someChildrenCompacted() )
        {
            return $this->doValuesCompacted('month',$eventType);
        }
    }
    
    function describeSingleAttributeValues( $attribute, $eventType = null )
    {
        
    }
    
    /** @return integer cached value forced read from compacted table */
    /** @todo duplicated */
    function getCompactedCount( $eventType = null, $attributes = array(), $unique = false )
    {
        $select = $this->select()
            ->from( $this->table('year_event'), 'SUM(`count`)' )
            ->where( '`unique` = ?', $unique ? 1 : 0 )
            ->filterByEventType( $eventType )
            ->filterByYear($this->getTimeParts())
            ->addCompactedAttributes( $this->getAttributes(), 'year' );
        return (int)$select->query()->fetchColumn();
    }
    
    function getUncompactedCount( $eventType=null, $attributes = array(), $unique = false )
    {
        if( $this->isInFuture() || !$this->allowUncompactedQueries )
        {
            return 0;
        }
        
        /** @todo duplicated in month */
        $select = $this->select();
        if( $this->someChildrenCompacted() )
        {
            $select->from( $this->table('month_event'), 'SUM(`count`)' )
                ->where( '`unique` = ?', $unique ? 1 : 0 )
                ->filterByEventType( $eventType )
                ->filterByYear($this->getTimeParts())
                ->addCompactedAttributes( count($attributes) ? $attributes : $this->getAttributes(), 'day' );
        }
        else
        {
            $select->from( $this->table('event'), $unique ? 'count(DISTINCT(`host`))' : 'count(*)' )
                ->filterByEventType( $eventType )
                ->filterByYear($this->getTimeParts())
                ->addUncompactedAttributes( count($attributes) ? $attributes : $this->getAttributes() );
        }
        
        return (int)$select->query()->fetchColumn();
    }
    
    /** @return array of the distinct attribute keys used for this time interval */
    function describeAttributeKeys( $eventType = null )
    {
        if( $this->someChildrenCompacted()  )
        {
             $keys = $this->doAttributeKeys('month',$eventType);
             return $keys;
        }
        
        return array();
        
    }
    
    function getTimeParts()
    {
        $return = array();
        $return['year'] = $this->timeParts['year'];
        return $return;
    }
    
    function isInFuture()
    {
        $now = new Zend_Date();
        if( $now->toString( Zend_Date::YEAR ) >= $this->timeParts['year'] )
        {
            return false;
        }
        return true;
    }
    
    function isInPresent()
    {
        $now = new Zend_Date();
        return $now->toString( Zend_Date::YEAR ) == $this->timeParts['year'];
    }
    
    function isInPast()
    {
        $now = new Zend_Date();
        if( $now->toString( Zend_Date::YEAR ) > $this->timeParts['year'] )
        {
            return true;
        }
        return false;
    }
    
    /** @todo duplicated in month */
    function childrenAreCompacted()
    {
        foreach( $this->getMonths() as $month )
        {
            if( !$month->hasBeenCompacted() )
            {
                return false;
            }
        }
        return true;
    }
    
    function someChildrenCompacted()
    {
        foreach( $this->getMonths() as $month )
        {
            if( $month->hasBeenCompacted() )
            {
                return true;
            }
        }
        return false;
    }
    
    function compactChildren()
    {
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return;
        }
        foreach( $this->getMonths() as $month )
        {
            if( $month->isInPast() && !$month->hasBeenCompacted() )
            {
                $month->compact();
            }
        }
    }
    
    function getMonths( $attributes = array() )
    {
        if( is_array( $this->months) && count($this->months) )
        {
            return $this->months;
        }
        $this->months = array();
        for( $month = 1; $month <= 12; $month++ )
        {
            $this->months[ $month ] = $this->getMonth( $month, $attributes );
        }
        return $this->months;
    }
    
    protected function describeEventTypeSql()
    {
        return $this->select()
            ->from( $this->table('month_event'), 'distinct(`event_type`)' )
            ->filterByYear($this->getTimeParts());
    }
    
    protected function getMonth( $month, $attributes = array() )
    {
        $attributes = count( $attributes ) ? $attributes : $this->getAttributes();
        $timeParts = array(
            'year' => $this->timeParts['year'],
            'month' => $month
        );
        return new PhpStats_TimeInterval_Month( $timeParts, $attributes, $this->autoCompact, $this->allowUncompactedQueries );
    }
    
    protected function doHasBeenCompacted()
    {
        $select = $this->select()
            ->from( $this->table('meta'), 'count(*)' )
            ->where( '`month` IS NULL' )
            ->filterByYear($this->getTimeParts());
        return (bool)$select->query()->fetchColumn();
    }
}