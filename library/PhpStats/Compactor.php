<?php
class PhpStats_Compactor extends PhpStats_Abstract
{
	private $outputLog;
	
	protected $lockToken;
	
    function compact( $outputLog = false )
    {
    	$this->outputLog = $outputLog;
    	
    	$this->acquireLock();
    
    	$start = $this->earliestNonCompactedHour();
    	$end = $this->latestnonCompactedHour();
    	
    	if( false === $start || $end === false )
    	{
			$this->log('no hours to compact');
    	}
    	else
    	{
			$this->log('enumerating hours from "hour ' . $start['hour'] . ' ' . $start['day'].'-'.$start['month'].'-'.$start['year'] . ' through hour ' . $end['hour'] . ' ' . $end['day'].'-'.$end['month'].'-'.$end['year']);
	        foreach( $this->enumerateHours( $start, $end ) as $hour )
	        {
	            $timeParts = $hour->getTimeParts();
	            $this->log('compacting hour '. $timeParts['hour'] . ' ('. $timeParts['day'].'-'.$timeParts['month'].'-'.$timeParts['year'].')');
	            $hour->compact();
	        }
		}
		
		$start = $this->earliestNonCompactedDay();
    	//$end = $this->latestNonCompactedD();
    	
    	if( false == $start )
    	{
			$this->log('no days to compact');
    	}
    	else
    	{
	        $this->log('enumerating days from ' . $start['day'].'-'.$start['month'].'-'.$start['year'] . ' through ' . $end['day'].'-'.$end['month'].'-'.$end['year']);
	        foreach( $this->enumerateDays( $start, $end ) as $day )
	        {
	            $timeParts = $day->getTimeParts();
	            $this->log('compacting day ' . $timeParts['day'].'-'.$timeParts['month'].'-'.$timeParts['year']);
	            $day->compact();
	        }
		}
		
		$this->freeLock();
    }
    
    function hasLock()
    {
    	if( !$this->lockToken )
    	{
			return false;
    	}
		$select = $this->db()->select()
			->from( $this->table('lock') )
			->where( 'token = ?', $this->lockToken );
		$row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
		if( !$row )
		{
			return false;
		}
		if( $this->lockToken == $row['token'] )
		{
			return true;
		}
		return false;
    }
    
    function acquireLock()
    {
    	if( $this->hasLock() )
    	{
			return;
    	}
    	
    	$select = $this->db()->select()
			->from( $this->table('lock'), 'count(*)' );
		if( $select->query()->fetchColumn() )
		{
			$msg = 'Some one else has the lock';
			$this->log($msg);
			throw new Exception( $msg );
		}
			
    	$rand = md5(uniqid());
		$this->lockToken = substr( $rand, 0, 20 );
		$this->db()->insert( $this->table('lock'), array( 'token' => $this->lockToken ) );
    }
    
    function freeLock()
    {
    	$cond = sprintf('token=\'%s\'',$this->lockToken);
		$this->db()->delete( $this->table('lock'), $cond );
    }
    
    function lastCompacted()
    {
        $select = $this->db()->select()
            ->from( $this->table('meta') )
            ->order( 'year DESC')
            ->order( 'month DESC')
            ->order( 'day DESC')
            ->order( 'hour DESC')
            ->limit( 1 );
        
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        if( $row )
        {
            if( !isset($row['hour']))
            {
                $row['hour'] = null;
            }
            return $row;
        }
        return false;
    }
    
    function lastCompactedDay()
    {
        $select = $this->db()->select()
            ->from( $this->table('meta') )
            ->order( 'year DESC')
            ->order( 'month DESC')
            ->order( 'day DESC')
            ->where( 'hour IS NULL')
            ->limit( 1 );
        
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        if( $row )
        {
            unset($row['hour']);
            return $row;
        }
        return false;
    }
    
    function earliestNonCompactedDay()
    {
        $select = $this->deltaNonCompactedDay('ASC');
        $earlistNonCompacted = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        
        $lastCompacted = $this->lastCompactedDay();
        
        if( !$earlistNonCompacted['day']  )
        {
			return false;
        }
        
        $day = new PhpStats_TimeInterval_Day( $earlistNonCompacted, array(), false, false );
        if( !$day->isInPast() )
        {
			return false;
        }

		if( !isset( $earlistNonCompacted['month'] ))
		{
			$earlistNonCompacted['month'] = $lastCompacted['month'];
		}
		if( !isset( $earlistNonCompacted['year'] ))
		{
			$earlistNonCompacted['year'] = $lastCompacted['year'];
		}
        
        return $earlistNonCompacted;
    }
    
    function earliestNonCompactedHour()
    {
        $select = $this->deltaNonCompactedHour('ASC');
        $earlistNonCompacted = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        
        if( false === $earlistNonCompacted )
        {
			return false;
        }
        $hour = new PhpStats_TimeInterval_Hour( $earlistNonCompacted, array(), false, false );
        if( !$hour->isInPast() )
        {
			return false;
        }
        $earlistNonCompacted['day'] = 1;
        return $earlistNonCompacted;
    }
    
    function latestnonCompactedHour()
    {
        $select = $this->deltaNonCompactedHour('DESC');
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        return $row;
    }
    function latestnonCompactedDay()
    {
        $select = $this->deltaNonCompactedDay('DESC');
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        return $row;
    }
    
    function enumerateHours( $start, $end )
    {
        if( $start['day'] == $end['day'] && $start['month'] == $end['month'] )
        {
            return $this->enumerateHoursSingleDay( $start, $end );
        }
        
        if( $start['month'] == $end['month'] )
        {
	        return $this->enumerateHoursSingleMonth( $start, $end );
		}
		
		$hours = $this->enumerateHoursForMonthAfter( $start );
		$start['month'] += 1;
        $hours = array_merge( $hours, $this->enumerateHoursBetweenMonths( $start, $end ) );
        $hours = array_merge( $hours, $this->enumerateHoursForMonthBefore( $end ) );
        return $hours;
        
    }
    
    protected function enumerateHoursSingleMonth( $start, $end )
    {
		$hours = $this->enumerateHoursForDayAfter( $start );
		$start['day'] += 1;
	    $hours = array_merge( $hours, $this->enumerateHoursBetweenDays( $start, $end ) );
	    $hours = array_merge( $hours, $this->enumerateHoursForDayBefore( $end ) );
	    return $hours;
    }
    
    function enumerateDays( $start, $end )
    {
        if( $start['month'] == $end['month'] )
        {
            return $this->enumerateDaysSingleMonth( $start, $end );
        }
        
        $days = $this->enumerateDayForMonthAfter( $start );
        $days = array_merge( $days, $this->enumerateDayBetweenMonths( $start, $end ) );
        $days = array_merge( $days, $this->enumerateDayForMonthBefore( $end ) );
        return $days;
    }
    
    function enumerateDaysSingleMonth( $start, $end )
    {
        $days = array();
        for( $day = $start['day']; $day <= $end['day']; $day++ )
        {
            $dayObj = new PhpStats_TimeInterval_Day( array(
                'day' => $day,
                'month' => $start['month'],
                'year' => $start['year']
            ));
            array_push( $days, $dayObj );
        }
        return $days;
    }
    
    private function deltaNonCompactedHour( $direction = 'ASC' )
    {
        $lastCompacted = $this->lastCompacted();
        $select = $this->db()->select()
            ->from( 'socks_event', array( 'hour', 'day', 'month', 'year' ) );
            if($lastCompacted )
            {
            	$where = '';
            	$where .= sprintf( "( hour > %d && day >= %d && month >= %d && year >= %d )", $lastCompacted['hour'], $lastCompacted['day'], $lastCompacted['month'], $lastCompacted['year'] );
            	$where .= sprintf( " OR ( day > %d && month >= %d && year >= %d )", $lastCompacted['day'], $lastCompacted['month'], $lastCompacted['year'] );
            	$where .= sprintf( " OR ( month > %d && year >= %d )", $lastCompacted['month'], $lastCompacted['year'] );
            	$where .= sprintf( " OR ( year > %d )", $lastCompacted['year'] );
                $select->where( $where );
            }
        $select
            ->order( 'year '.$direction)
            ->order( 'month '.$direction)
            ->order( 'day '.$direction)
            ->order( 'hour '.$direction)            
            ->limit(1);
        
        return $select;
    }
    
    private function deltaNonCompactedDay( $direction = 'ASC' )
    {
        $lastCompacted = $this->lastCompactedDay();
        $select = $this->db()->select()
            ->from( 'socks_event', array( 'day', 'month', 'year' ) );
            if($lastCompacted )
            {
            	$where = '';
            	$where .= sprintf( " ( day > %d && month >= %d && year >= %d )", $lastCompacted['day'], $lastCompacted['month'], $lastCompacted['year'] );
            	$where .= sprintf( " OR ( month > %d && year >= %d )", $lastCompacted['month'], $lastCompacted['year'] );
            	$where .= sprintf( " OR ( year > %d )", $lastCompacted['year'] );
                $select->where( $where );
            }

        $select
            ->order( 'year '.$direction)
            ->order( 'month '.$direction)
            ->order( 'day '.$direction)
            ->limit(1);
        return $select;
    }
    
    private function enumerateHoursSingleDay( $start, $end )
    {
        $hours = array();
        for( $hour = $start['hour']; $hour <= $end['hour']; $hour++ )
        {
            $hourObj = new PhpStats_TimeInterval_Hour( array(
                'hour' => $hour,
                'day' => $start['day'],
                'month' => $start['month'],
                'year' => $start['year']
            ));
            array_push( $hours, $hourObj );
        }
        return $hours;
    }
    
    private function enumerateHoursForDayAfter($timeParts)
    {
        $end['hour'] = 23;
        $end['day'] = $timeParts['day'];
        $end['month'] = $timeParts['month'];
        $end['year'] = $timeParts['year'];
        $timeParts['hour'] = 0;
        return $this->enumerateHoursSingleDay( $timeParts, $end );
    }
    
    private function enumerateHoursForDayBefore($timeParts)
    {
        $start['hour'] = 0;
        $start['day'] = $timeParts['day'];
        $start['month'] = $timeParts['month'];
        $start['year'] = $timeParts['year'];
        $timeParts['hour'] = 0;
        return $this->enumerateHoursSingleDay( $start, $timeParts );
    }
    
    private function enumerateHoursBetweenMonths( $start, $end )
    {
        $hours = array();
        for( $month = $start['month']; $month <= $end['month']; $month++ )
        {
            $start2 = array(
                'hour' => 0,
                'day' => 1,
                'month' => $month,
                'year' => $start['year']
            );
            $end2 = array(
                'hour' => 23,
                'day' => cal_days_in_month( CAL_GREGORIAN, $end['month'], $end['year'] ),
                'month' => $month,
                'year' => $start['year']
            );
            $hours = array_merge( $hours, $this->enumerateHoursBetweenDays( $start2, $end2 ) );
        }
        return $hours;
    }
    
    private function enumerateHoursBetweenDays( $start, $end )
    {
        $hours = array();
        for( $day = $start['day']; $day <= $end['day']; $day++ )
        {
            $start2 = array(
                'hour' => 0,
                'day' => $day,
                'month' => $start['month'],
                'year' => $start['year']
            );
            $end2 = array(
                'hour' => 23,
                'day' => $day,
                'month' => $start['month'],
                'year' => $start['year']
            );
            $hours = array_merge( $hours, $this->enumerateHours( $start2, $end2 ) );
        }
        return $hours;
    }
    
    private function enumerateDayForMonthAfter($timeParts)
    {
        $end['day'] = cal_days_in_month( CAL_GREGORIAN, $timeParts['month'], $timeParts['year'] );
        $end['month'] = $timeParts['month'];
        $end['year'] = $timeParts['year'];
        return $this->enumerateDaysSingleMonth( $timeParts, $end );
    }
    
    private function enumerateHoursForMonthAfter($timeParts)
    {
        $end['day'] = cal_days_in_month( CAL_GREGORIAN, $timeParts['month'], $timeParts['year'] );
        $end['month'] = $timeParts['month'];
        $end['year'] = $timeParts['year'];
        return $this->enumerateHoursSingleMonth( $timeParts, $end );
    }
    
    private function enumerateDayForMonthBefore($timeParts)
    {
        $start['day'] = 1;
        $start['month'] = $timeParts['month'];
        $start['year'] = $timeParts['year'];
        return $this->enumerateDaysSingleMonth( $start, $timeParts );
    }
    
    private function enumerateHoursForMonthBefore($timeParts)
    {
        $start['day'] = 1;
        $start['month'] = $timeParts['month'];
        $start['year'] = $timeParts['year'];
        return $this->enumerateHoursSingleMonth( $start, $timeParts );
    }
    
    private function enumerateDayBetweenMonths( $start, $end )
    {
        $days = array();
        for( $month = $start['month']+1; $month < $end['month']; $month++ )
        {
            $start2 = array(
                'day' => 1,
                'month' => $month,
                'year' => $start['year']
            );
            $end2 = array(
                'day' => cal_days_in_month( CAL_GREGORIAN, $month, $start['year'] ),
                'month' => $month,
                'year' => $start['year']
            );
            $days = array_merge( $days, $this->enumerateDays( $start2, $end2 ) );
        }
        return $days;
    }
    
    private function log( $msg )
    {
        if( $this->outputLog )
        {
			echo date('r') . '> ' . $msg . "\n";
        }
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}