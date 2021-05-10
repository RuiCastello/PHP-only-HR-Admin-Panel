<?php


class Employee implements IEmployee{

    private $firstName;
    private $lastName;
    private $department;
    private $birthDate;
    private $hireDate;
    private $gender;
    private $cc;
    private $employeeNumber;
    private $role;

    public function __construct($firstName, $lastName, $birthDate, $role, $department){
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->birthDate = $birthDate;
        $this->role = $role;
        $this->department = $department;

    }

    // Getters
    public function getName(){
        return (ucfirst($this->firstName) ." ". ucfirst($this->lastName));
    }
    public function getFirstName(){
        return $this->firstName;
    }
    public function getLastName(){
        return $this->lastName;
    }
    public function getBirthDate(){
        return $this->birthDate;
    }
    public function getHireDate(){
        return $this->hireDate;
    }
    public function getGender(){
        return $this->gender;
    }
    public function getRole(){
        return $this->role;
    }
    public function getCC(){
        return $this->cc;
    }
    public function getEmployeeNumber(){
        return $this->employeeNumber;
    }
    public function getDepartment(){
        return $this->department;
    }

  


    // Setters
    public function setFirstName($firstName){
        $this->firstName = $firstName;
    }
    public function setLastName($lastName){
        $this->lastName = $lastName;
    }
    public function setBirthDate($birthDate){
        $this->birthDate = $birthDate;
    }
    public function setHireDate($hireDate){
        $this->hireDate = $hireDate;
    }
    public function setGender($gender){
        $this->gender = $gender;
    }
    public function setRole($role){
        $this->role = $role;
    }
    public function setCC($cc){
        $this->cc = $cc;
    }
    public function setEmployeeNumber($employeeNumber){
        $this->employeeNumber = $employeeNumber;
    }
    public function setDepartment($departmentNumber){
        $this->department = $departmentNumber;
    }


    
}