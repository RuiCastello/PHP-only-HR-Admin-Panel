<?php


class Department{

    public $departmentNumber;
    public $departmentName;
    // private $DB;

    public function __construct($departmentNumber, $departmentName = ""){
        $this->departmentNumber = $departmentNumber;
        $this->departmentName = $departmentName;
        // $this->DB = $DB;
    }

    // public function getEmployees()
    // {
    //     return $this->DB->getEmployeesDepartment($this->departmentNumber);
    // }

    
}