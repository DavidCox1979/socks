<?php
class PhpStats_Report
{
    /** @var array */
    protected $timeParts;
    
    /** @param array $timeparts (hour, month, year, day ) */
    public function __construct( $timeParts )
    {
        $this->timeParts = $timeParts;
    }
    
    /**
    * @param string $eventType ( ex. click, search_impression )
    * @return PDO_Statement|Zend_Db_Statement
    */
    public function getCount( $eventType )
    {
        $select = $this->db()->select()
            ->from( 'event', 'count(*)' );
        if( isset($this->timeParts['month']) )
        {
            $select->where( 'MONTH(datetime) = ?', $this->timeParts['month'] );
        }
        if( isset($this->timeParts['hour']) )
        {
            $select->where( 'HOUR(datetime) = ?', $this->timeParts['hour'] );
        }
        return $select->query()->fetchColumn();
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}