<?php
/**
* Reports are "partitioned" by their time intervals & custom attributes
* 
* @license This source file is subject to the new BSD license that is bundled
* with this package in the file LICENSE.txt.
*/
abstract class PhpStats_TimeInterval_Abstract extends PhpStats_Abstract implements PhpStats_TimeInterval
{
    /** @var array */
    protected $timeParts;
    
    /** @var array */
    protected $attributes;
    
    /** @var Zend_Db_Select */
    protected $select;
    
    /** @var bool */
    protected $autoCompact;
    protected $allowUncompactedQueries;
    
    /** @var mixed - null or array */
    protected $attribValues;
    protected $attribValuesAll;
    protected $attribKeys;
    
    protected $in_process_of_getting_attributes = false;
    
    /** @var bool */
    protected $has_been_compacted;
    
    /** @var string name of this interval (example hour, day, month, year) */
    protected $interval;
    
    /** @var string name of this interval's child (example hour, day, month) */
    protected $interval_child;
    
    /** @var string name of this interval's parent (example day, month, year) */
    protected $interval_parent;
    
    /**
    * @param array $timeparts (hour, month, year, day )
    * @param array $attributes only records that match these
    *   attributes & values will be included in the report
    */
    function __construct( $timeParts, $attributes = array(), $autoCompact = true, $allowUncompactedQueries = true )
    {
        $this->autoCompact = $autoCompact;
        $this->allowUncompactedQueries = $allowUncompactedQueries;
        $this->setTimeParts( $timeParts );
        $this->attributes = $attributes;
    }
    
    function canCompact()
    {
		if( $this->hasAttributes() )
    	{
			throw new Exception( 'May not compact while filtering on attributes' );
    	}
        if( !$this->allowUncompactedQueries )
    	{
			 throw new Exception( 'You must allow uncompacted queries in order to compact an interval' );
    	}
    	if( $this->hasBeenCompacted() )
        {
            return false;
        }
        if( $this->isInFuture() || $this->isInPresent() )
        {
            return false;
        }
        return true;
    }
    
    function hasAttributes()
    {
		if( !is_array($this->getAttributes()) )
		{
			return false;
		}
		foreach( $this->getAttributes() as $value )
		{
			if( $value )
			{
				return true;
			}
		}
		return false;
    }
    
    function getAttributes()
    {
        if( $this->in_process_of_getting_attributes )
        {
            return;
        }
        $this->in_process_of_getting_attributes = true;
        if( $this->hasZeroCount() )
        {
            return;
        }
        foreach( $this->describeAttributeKeys() as $attribute )
        {
            if( !isset( $this->attributes[$attribute] ) )
            {
                $this->attributes[$attribute] = null;
            }
        }
        $this->in_process_of_getting_attributes = false;
        return $this->attributes;
    }
    
    protected function hasZeroCount()
    {
        return false;
    }
    
    /** @return boolean wether or not this time interval has been previously compacted */
    function hasBeenCompacted()
    {
        if( isset($this->has_been_compacted) )
        {
            return $this->has_been_compacted;
        }
        return $this->has_been_compacted = $this->doHasBeenCompacted();
    }
    
    abstract protected function doHasBeenCompacted();
    
    function getTimeParts()
    {
        return $this->timeParts;
    }
    
    /** Compacts the day and each of it's hours */
	function compact()
	{
		if( !$this->canCompact() )
		{
			return;
		}
		if( !$this->allowUncompactedQueries )
		{
			 throw new Exception( 'You must allow uncompacted queries in order to compact an interval' );
		}
		
		if( $this->hasBeenCompacted() || $this->isInFuture() || $this->isInPresent() )
		{
			return;
		}
		
		if( $this->hasZeroCount() )
		{
			$this->markAsCompacted();
			return;
		}

		if( !$this->someChildrenCompacted() )
		{
			$this->compactChildren();
		}
		
		if( !count( $this->describeAttributesValues() ) )
		{
			$this->doCompact( $this->interval.'_event' );
		}
		else
		{
			$this->doCompactAttributes( $this->interval.'_event' );
		}
		$this->markAsCompacted();
	}
    
    /**
    * Gets the number of records for this interval, event type, and attributes combination
    * 
    * @param string $eventType
    * @param array of attributes
    * @param boolean $unique set to true to count each hostname/IP Address only once. Defaults to false.
    * 
    * @return integer additive value
    */
    function getCount( $eventType = null, $attributes = array(), $unique = false )
    {
        $attributes = count($attributes) ? $attributes : $this->getAttributes();
        
        if( $this->isInPast() && $this->hasBeenCompacted() )
        {
            return $this->getCompactedCount( $eventType, $attributes, $unique );   
        }
        
        $count = $this->getUncompactedCount( $eventType, $attributes, $unique );
        if( $this->shouldCompact() && $this->autoCompact )
        {
            $this->compact();
        }
        return $count;
    }
    
    /** @return array of distinct event_types that have been used during this TimeInterval */
    function describeEventTypes()
    {
    	if( $this->notCompactedAndCannotHitUncompactedTable() )
    	{
			return array();
    	}
        if( $this->autoCompact && !$this->childrenAreCompacted() )
        {
        	$this->compactChildren();
		}
        $select = $this->describeEventTypeSql();
        $rows = $select->query( Zend_Db::FETCH_OBJ )->fetchAll();
        $eventTypes = array();
        foreach( $rows as $row )
        {
            array_push( $eventTypes, $row->event_type );
        }
        return $eventTypes;
    }
    
    /** @return array of the distinct attribute keys used for this time interval */
    /* @todo rename to describeAttributeKeysUncompacted() */
    function describeAttributeKeys( $eventType = null )
    {
        if( isset( $this->attribKeys[$eventType] ) && !is_null( $this->attribKeys[$eventType] ) )
    	{
			return $this->attribKeys[$eventType];
    	}
        $this->attribKeys[$eventType] = array();
        
        if( $this->autoCompact && !$this->hasBeenCompacted() )
        {
            $this->compactChildren();
        }
        if( $this->notCompactedAndCannotHitUncompactedTable() )
    	{
			return array();
    	}
        
        $select = $this->select()->from( $this->table('event_attributes'), 'distinct(`key`)' )
            ->joinAttributesTable()
            ->filterByTimeParts( $this->getTimeParts() )
            ->filterByEventType( $eventType );
        
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        foreach( $rows as $row )
        {
            array_push( $this->attribKeys[$eventType], $row[0] );
        }
        return $this->attribKeys[$eventType];
    }
    
    function describeAttributesValuesCombinations( $eventType = null )
    {
        $array = $this->describeAttributeKeys();
        
        // initialize by adding the empty set
        $results = array(array( ));

        foreach ($array as $element)
        {
            foreach ($results as $combination)
            {
                foreach( $this->describeSingleAttributeValues( $element, $eventType ) as $value )
                {
                    $merge = array_merge(array( $element => (string)$value ), $combination);
                    array_push($results, $merge);
                }
            }
        }
        
        // ensure null is set for empty ones
        foreach( $results as $index => $result )
        {
            foreach( $array as $attrib )
            {
                if( !isset( $results[$index][$attrib] ))
                {
                    $results[$index][$attrib] = null;
                }
            }
        }

        return $results;
    }
    
    abstract function describeSingleAttributeValues( $attribute, $eventType = null );
    
    function isInPast()
    {
        return false;
    }
    
    function autoCompact()
    {
		return $this->autoCompact;
    }
    
    function compactChildren()
    {	
    }
    
    /** @return integer cached value forced read from compacted table */
    abstract function getCompactedCount( $eventType = null, $attributes = array(), $unique = false ); 
    
    /** @return integer value forced read from uncompacted table */
    abstract function getUncompactedCount( $eventType = null, $attributes = array(), $unique = false );
    
    abstract function isInFuture();
    abstract function isInPresent();
    
    abstract function childrenAreCompacted();
    abstract function someChildrenCompacted();
    
    protected function select()
    {
        $select = new PhpStats_Select( $this->db() );
        return $select;
    }
    
    protected function doCompact( $table )
    {
        foreach( $this->describeEventTypes() as $eventType )
        {
            // non-unique
            $bind = $this->getTimeParts();
            $bind['event_type'] = $eventType;
            $bind['count'] = $this->getUncompactedCount( $eventType );
            $this->db()->insert( $this->table($table), $bind );
            
            // unique
            $bind = $this->getTimeParts();
            $bind['event_type'] = $eventType;
            $bind['count'] = $this->getUncompactedCount( $eventType, array(), true );
            $bind['unique'] = 1;
            $this->db()->insert( $this->table($table), $bind );
        }
    }
    
    /** @todo duplicated in day */
    protected function doCompactAttributes( $table )
    {
        $cols = array(
            'count' => 'SUM(`count`)',
            'event_type',
            'unique',
            'attribute_keys',
            'attribute_values'
        );
        $select = $this->select()
            ->from( $this->table($this->interval_child.'_event'), $cols )
            ->group('attribute_values')
            ->group( 'unique' )
            ->group( 'event_type' )
            ->filterByTimeParts($this->getTimeParts());
        
        $result = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_OBJ );
        foreach( $result as $row )
        {
            $bind = $this->getTimeParts();
            $bind['event_type'] = $row->event_type;
            $bind['unique'] = $row->unique;
            $bind['count'] = $row->count;
            $bind['attribute_keys'] = implode( ',', $this->describeAttributeKeys() );
            $bind['attribute_values'] = $row->attribute_values;
            $this->db()->insert( $this->table($table), $bind );
        }
    }
    
    protected function doValuesCompacted( $grain = 'day', $eventType )
    {   
        $select = $this->select()
            ->from( "socks_{$grain}_event", array('DISTINCT(attribute_values)') )
            ->filterByEventType( $eventType)
            ->filterByTimeParts( $this->getTimeParts() )
            ->addCompactedAttributes( $this->getAttributes(), $grain, false );
        
        $rows = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_NUM );
        $values = array();
        foreach( $rows as $row )
        {
            $row[0] = explode( ';', $row[0] );
            foreach( $row[0] as $string )
            {
                list( $attribute, $value ) = $this->unserializeKeyValue($string.';');
                if(!empty( $value ))
                {
                    $values[$attribute][] = $value;
                }
            }
        }
        return $values;
    }
    
    protected function doValuesUncompacted( $eventType = null )
    {
        $values = array();
        foreach( $this->describeAttributeKeys($eventType) as $attribute )
        {
            $values[$attribute] = $this->doSingleAttributeValuesUncompacted($attribute, $eventType);
        }
        return $values;
    }
    
    protected function doSingleAttributeValuesUncompacted( $attribute, $eventType = null )
    {
        $select = $this->select()
            ->from( $this->table('event_attributes'), 'distinct(`value`)' )
            ->where( '`key` = ?', $attribute )
            ->filterByEventType( $eventType )
            ->filterByTimeParts( $this->getTimeParts() )
            ->joinAttributesTable();
        $select->addUncompactedAttributes( $this->getAttributes() );
        $select = preg_replace( '#FROM `(.*)`#', 'FROM `$1` FORCE INDEX (key_2)', $select, 1 );
        
        $values = array();
        $rows = $this->db()->query( $select )->fetchAll( Zend_Db::FETCH_NUM );
        foreach( $rows as $row )
        {
            if( !is_null($row[0]) )
            {
                array_push( $values, $row[0] );
            }
        }
        return $values;
    }
    
    protected function markAsCompacted()
    {
        if( !$this->hasBeenCompacted() )
        {
            $this->has_been_compacted = true;
            $this->db()->insert( $this->table('meta'), $this->getTimeParts() );
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
    
    protected function shouldCompact()
    {
        if( $this->isInFuture() || $this->isInPresent() )
        {
            return false;
        }
        return true;
    }
    
    /** @throws PhpStats_TimeInterval_Exception_MissingTime */
    protected function setTimeParts( $timeParts )
    {
        $this->timeParts = $timeParts;
    }
    
    protected function eventTable( $tablePrefix = '' )
	{
		return $this->table( $tablePrefix ) . '_event';
	}
	
	protected function attributeTable( $tablePrefix = '' )
	{
		$table = ( $tablePrefix ? $tablePrefix . '_' : '' ) . 'event_attributes';
		return $this->table( $table );
	}
    
    protected function notCompactedAndCannotHitUncompactedTable()
    {
		return !$this->autoCompact && !$this->hasBeenCompacted() && !$this->allowUncompactedQueries && !$this->childrenAreCompacted() && !$this->someChildrenCompacted();
    }
    
    protected function unserializeKeyValue($string)
    {
        preg_match( '#:(.*?):(.*?);#', $string, $matches );
        if( 2 > count($matches) )
        {
            $matches = array( 1=>'', 2=>'' );
        }
        return array( $matches[1], $matches[2] );
    }
    
    protected function serializeKeyValue( $attribute, $value )
    {
        return ':' . $attribute . ':' . $value . ';';
    }

    protected function doAttributeKeys( $grain, $eventType = null )
    {
        $select = $this->select()
            ->from( 'socks_'.$grain.'_event', array('DISTINCT( attribute_keys )') )
            ->filterByTimeParts($this->getTimeParts())
            ;//->addCompactedAttributes($this->getAttributes()); @todo write test & un-comment
        $rows = $select->query( Zend_Db::FETCH_NUM )->fetchAll();
        $keys = array();
        foreach( $rows as $row )
        {
            foreach( explode(',', $row[0] ) as $key )
            {
                if( !empty($key) )
                {
                    array_push( $keys, $key );
                }
            }
        }
        return $keys;
    }
    
    abstract protected function describeEventTypeSql();
}
