<?php

 interface IEmployee {

    public function setDepartment($departmentNumber);

    public function getDepartment();

    public function setBirthDate($birthDate);

    public function getBirthDate();

    public function setHireDate($hireDate);

    public function getHireDate();

    public function setFirstName($firstName);

    public function getFirstName();

    public function setLastName($lastName);

    public function getLastName();
    
    public function getName();

    public function setGender($gender);

    public function getGender();

    public function setCC($cc);

    public function getCC();



 }