<?php
class PhpStats_Compactor
{
    /** @var PhpStats_Report */
    protected $report;
    
    public function __construct( $report )
    {
        $this->report = $report;
    }
    
    public function compact()
    {
        foreach( $this->report->getHours() as $hour )
        {
            $clicks = $hour->getCount('clicks');
            $bind = array(
            );
            $this->db()->insert('hour_event', $bind );
        }
        
    }
    
    /** @return Zend_Db_Adapter_Abstract */
    protected function db()
    {
        return Zend_Registry::get('db');
    }
}