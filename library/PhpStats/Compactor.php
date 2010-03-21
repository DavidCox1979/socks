<?php
class PhpStats_Compactor extends PhpStats_Abstract
{
	private $outputLog;
	
    function compact( $start, $end, $outputLog = false )
    {
    	$this->outputLog = $outputLog;
        $this->log('compacting hours');
        
        foreach( $this->enumerateHours( $start, $end ) as $hour )
        {
            $timeParts = $hour->getTimeParts();
            $this->log('compacting hour '. $timeParts['hour'] . ' ('. $timeParts['day'].'-'.$timeParts['month'].'-'.$timeParts['year'].')');
            $hour->compact();
        }
        foreach( $this->enumerateDays( $start, $end ) as $day )
        {
            $timeParts = $hour->getTimeParts();
            $this->log('compacting day ' . $timeParts['day'].'-'.$timeParts['month'].'-'.$timeParts['year']);
            $day->compact();
        }
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
    
    function earliestNonCompacted()
    {
        $select = $this->deltaCompacted('ASC');
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        return $row;
    }
    
    function latestNonCompacted()
    {
        $select = $this->deltaCompacted('DESC');
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        return $row;
    }
    
    function enumerateHours( $start, $end )
    {
        $this->log('enumerating hours from ' . $start['day'].'-'.$start['month'].'-'.$start['year'] . ' through' . $end['day'].'-'.$end['month'].'-'.$end['year']);
        
        if( $start['day'] == $end['day'] )
        {
            return $this->enumerateHoursSingleDay( $start, $end );
        }
        
        $hours = $this->enumerateHoursForDayAfter( $start );
        $hours = array_merge( $hours, $this->enumerateHoursBetweenDays( $start, $end ) );
        $hours = array_merge( $hours, $this->enumerateHoursForDayBefore( $end ) );
        
        return $hours;
        
    }
    
    function enumerateDays( $start, $end )
    {
        $this->log('enumerating days from ' . $start['day'].'-'.$start['month'].'-'.$start['year'] . ' through' . $end['day'].'-'.$end['month'].'-'.$end['year']);
        
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
    
    private function deltaCompacted( $direction = 'ASC' )
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
            ->order( 'day '.$direction)
            ->order( 'hour '.$direction)            
            ->order( 'month '.$direction)
            ->order( 'year '.$direction)
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
        return $this->enumerateHoursSingleDay( $timeParts, $end );
    }
    
    private function enumerateHoursForDayBefore($timeParts)
    {
        $start['hour'] = 0;
        $start['day'] = $timeParts['day'];
        $start['month'] = $timeParts['month'];
        $start['year'] = $timeParts['year'];
        return $this->enumerateHoursSingleDay( $start, $timeParts );
    }
    
    private function enumerateHoursBetweenDays( $start, $end )
    {
        $hours = array();
        for( $day = $start['day']+1; $day < $end['day']; $day++ )
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
    
    private function enumerateDayForMonthBefore($timeParts)
    {
        $start['day'] = 1;
        $start['month'] = $timeParts['month'];
        $start['year'] = $timeParts['year'];
        return $this->enumerateDaysSingleMonth( $start, $timeParts );
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