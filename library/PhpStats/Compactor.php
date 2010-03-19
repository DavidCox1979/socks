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
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}