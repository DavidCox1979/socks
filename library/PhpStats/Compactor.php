<?php
class PhpStats_Compactor extends PhpStats_Abstract
{
    function lastCompacted()
    {
        $select = $this->db()->select()
            ->from( $this->table('meta') )
            ->order( 'year DESC')
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
        $lastCompacted = $this->lastCompacted();
        $select = $this->db()->select()
            ->from( 'socks_event', array(
                'HOUR(`datetime`) as hour',
                'DAY(`datetime`) as day',
                'MONTH(`datetime`) as month',
                'YEAR(`datetime`) as year'
            ))
            ->where( 'HOUR(`datetime`) > ?', $lastCompacted['hour'] )
            ->where( 'DAY(`datetime`) >= ?', $lastCompacted['day'] )
            ->where( 'MONTH(`datetime`) >= ?', $lastCompacted['month'] )
            ->where( 'YEAR(`datetime`) >= ?', $lastCompacted['year'] );
        $row = $select->query( Zend_Db::FETCH_ASSOC )->fetch();
        return $row;
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}