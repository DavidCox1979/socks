<?php
class PhpStats_Compactor
{
    /** @var PhpStats_Report_Day */
    protected $report;
    
    public function __construct( $report )
    {
        $this->report = $report;
    }
    
    public function compact()
    {
        foreach( $this->report->getHours() as $hour )
        {
            $hour->compact();
        }
    }
    
    
}