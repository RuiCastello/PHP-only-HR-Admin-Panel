<?php


class Salary{

    public $empNo;
    public $fromDate;
    public $toDate;
    public $salary;

    public function __construct($empNo, $salary, $fromDate, $toDate){
        $this->empNo = $empNo;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->salary = $salary;
      
    }


    
}