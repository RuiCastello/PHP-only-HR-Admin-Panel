<?PHP 


class DB{
    
    private static $servername = "";  // "localhost" for example
    private static $username = "";
    private static $password = "";
    private static $dbname = "";
    
    protected static $conn;
    
    public function __construct(){
        
        //create connection
        //Para aceder a uma static variable pode-se fazer self:: ou NomeDaClass:: (DB:: - neste caso)
        self::$conn = new mysqli(self::$servername, DB::$username, self::$password, DB::$dbname);
        
        if (self::$conn->connect_error) {
            // die("Connection failed: ". $conn->connect_error);
            throw new Exception(self::$conn->connect_error);
        }
        
		// SET CHARSET TO UTF-8
		self::$conn->set_charset("utf8mb4");
    }
    
    
    
    
    public function getCurrentEmployeesByDepartment($departmentNumber)
    {
        $sql = "SELECT emp_no FROM dept_emp WHERE emp_no = '".$departmentNumber."' AND from_date < CURRENT_DATE() AND to_date > CURRENT_DATE() ";
        
        $result = self::$conn->query($sql);
        // echo "\n\$result\n";
        // var_dump($result);
        
        if(isset($result) && $result){
            
            $returnResult = []; // ou array()
            while ($row = $result->fetch_assoc()){
                $employeeNumber = $row['emp_no'];                
                array_push($returnResult,  $employeeNumber);
            }
            $result->close();
        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
    } // end getCurrentEmployeesByDepartment()
    
    
    
    
    
    public function addDepartment(Department $departmentObj){
        
        if ( isset($departmentObj) && !empty($departmentObj) ) {
            
            
            //First check if department already exists in DB
            $departmentExists = $this->checkDepartmentExists($departmentObj);
            
            if ($departmentExists == false)
            {
                
                $sql = "INSERT INTO departments (dept_no, dept_name) VALUES 
                ('".$departmentObj->departmentNumber."', 
                '".$departmentObj->departmentName."')";
                
                
                if (self::$conn->query($sql) === TRUE) {
                    // echo "Department has been added to the database.";
                    $lastDepartmentID = $departmentObj->departmentNumber;
                } else {
                    echo "Error: " . $sql . "\n" . self::$conn->error;
                }
                
                return $lastDepartmentID;
            }
            else return "already_exists";
            
        }
    }//end addDepartment()
    
    
    public function checkDepartmentExists($departmentObj)
    {  
        $returnResult = false;
        
        $sql = "SELECT * FROM departments WHERE dept_name = '".$departmentObj->departmentName."' OR dept_no = '".$departmentObj->departmentNumber."'";
        
        $result = self::$conn->query($sql);
        
        if(isset($result)){
            
            $num_rows = $result->num_rows;
            if ($num_rows > 0){
                $returnResult = true;
            }
            
            $result->close();
        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
    }
    
    public function getDepartmentsList()
    {
        $sql = "SELECT * FROM departments";
        
        $result = self::$conn->query($sql);
        // echo "\n\$result\n";
        // var_dump($result);
        
        if(isset($result) && $result){
            
            $returnResult = []; // ou array()
            while ($row = $result->fetch_assoc()){
                
                $departmentNumber = $row['dept_no'];
                $departmentName = $row['dept_name'];
                $newDepartment = new Department($departmentNumber, $departmentName);
                
                array_push($returnResult, $newDepartment);
            }
            $result->close();
        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
        
    } // end getDepartmentsList()
    
    
    
    
    public function addEmployee(Employee $employeeObj){
        $lastEmployeeInsertID = -1;
        
        if ( isset($employeeObj) && !empty($employeeObj) ) {
            
            $sql = "INSERT INTO employees (first_name, last_name, gender, hire_date, birth_date, cc) VALUES 
            ('".$employeeObj->getFirstName()."', 
            '".$employeeObj->getLastName()."',
            '".$employeeObj->getGender()."',
            '".$employeeObj->getHireDate()."',
            '".$employeeObj->getBirthDate()."',
            '".$employeeObj->getCC()."')";
            
            
            if (self::$conn->query($sql) === TRUE) {
                // echo "Employee has been added to the database.";
                $lastEmployeeInsertID = self::$conn->insert_id;
                
                //Se for manager, insere em department manager, se for employee, insere em department employee
                if($employeeObj->getRole() == "manager")
                {
                    $this->addDepartmentManager($employeeObj, $lastEmployeeInsertID);
                }
                else $this->addDepartmentEmployee($employeeObj, $lastEmployeeInsertID);
                
            } else {
                echo "Error: " . $sql . "\n" . self::$conn->error;
            }
            
            return $lastEmployeeInsertID;
        }
    }//end addEmployee()
    
    
    
    
    
    public function addDepartmentManager(Employee $employeeObj, $employeeID){
        $result = false;
        
        if ( isset($employeeObj) && !empty($employeeObj) ) {
            
            $toDate = intval($employeeObj->getHireDate()) + strtotime('1 year');
            $toDate = date('Y-m-d', $toDate);
            
            $sql = "INSERT INTO dept_manager (emp_no, dept_no, from_date, to_date) VALUES 
            ('".$employeeID."', 
            '".$employeeObj->getDepartment()."',
            '".$employeeObj->getHireDate()."',
            '".$toDate."')
            ";
            
            
            if (self::$conn->query($sql) === TRUE) {
                // echo "Employee has been added to the database.";
                $result = true;
            } else {
                echo "Error: " . $sql . "\n" . self::$conn->error;
            }
            
            return $result;
        }
    }//end addDepartmentManager()
    
    
    public function addDepartmentEmployee(Employee $employeeObj, $employeeID){
        $result = false;
        
        if ( isset($employeeObj) && !empty($employeeObj) ) {
            
            $toDate = intval($employeeObj->getHireDate()) + strtotime('1 year');
            $toDate = date('Y-m-d', $toDate);
            
            $sql = "INSERT INTO dept_emp (emp_no, dept_no, from_date, to_date) VALUES 
            ('".$employeeID."', 
            '".$employeeObj->getDepartment()."',
            '".$employeeObj->getHireDate()."',
            '".$toDate."')
            ";
            
            
            if (self::$conn->query($sql) === TRUE) {
                // echo "Employee has been added to the database.";
                $result = true;
            } else {
                echo "Error: " . $sql . "\n" . self::$conn->error;
            }
            
            return $result;
        }
    }//end addDepartmentEmployee()
    
    
    
   public function editDepartmentEmployee(Employee $employeeObj){

        if ( isset($employeeObj) && !empty($employeeObj) ) {
            $employeeID = $employeeObj->getEmployeeNumber();
            $newDepartmentNumber = $employeeObj->getDepartment();
            $newRole = $employeeObj->getRole();
            
            $old_employee_data = $this->getEmployee($employeeID);
            // echo '<pre>' . var_dump($old_employee_data) . '</pre>';
            // echo '<pre>' . var_export($old_employee_data[0], true) . '</pre>';
            
            // If the department number has been edited, then change it in the DB
            if ($old_employee_data[0]->getDepartment() != $newDepartmentNumber || $old_employee_data[0]->getRole() != $newRole)
            {
                $sql_delete = "DELETE FROM dept_emp WHERE emp_no = '".$employeeID."' ";
                self::$conn->query($sql_delete);
                
                $sql_delete2 = "DELETE FROM dept_manager WHERE emp_no = '".$employeeID."' ";
                self::$conn->query($sql_delete2);

                if($employeeObj->getRole() == "manager")
                {
                    $this->addDepartmentManager($employeeObj, $employeeID);
                }
                else $this->addDepartmentEmployee($employeeObj, $employeeID);
            }
        }
    }//end editDepartmentEmployee()
    
    
    public function editEmployee($employeeObj){
        if ( isset($employeeObj) && !empty($employeeObj) ) {
            
            $sql = "UPDATE employees
            SET first_name = '".$employeeObj->getFirstName()."',
            last_name = '".$employeeObj->getLastName()."',
            gender = '".$employeeObj->getGender()."',
            hire_date = '".$employeeObj->getHireDate()."',
            birth_date = '".$employeeObj->getBirthDate()."',
            cc = '".$employeeObj->getCC()."'
            WHERE emp_no = '".$employeeObj->getEmployeeNumber()."';
            ";
        }
        
        $result = self::$conn->query($sql);
        
        
        if (self::$conn->query($sql) === TRUE) {
            
            $this->editDepartmentEmployee($employeeObj);

            // echo "Employee's data edits have been applied to the database.";
        } else {
            // echo "Error: " . $sql . "\n" . self::$conn->error;
        }
        //$result->close();
    }//end editEmployee()
    
    
    
    
    public function checkEmployeeExists($employeeObj)
    {  
        $returnResult = false;
        
        $sql = "SELECT first_name FROM employees 
        WHERE first_name = '".$employeeObj->getFirstName()."' 
        AND last_name = '".$employeeObj->getLastName()."'
        AND birth_date = '".$employeeObj->getBirthDate()."'
        ";
        
        $result = self::$conn->query($sql);
        //  var_dump($sql);
        //  var_dump($result);
        if(!empty($result)){
            
            $num_rows = $result->num_rows;
            if ($num_rows > 0){
                $returnResult = true;
            }
            
            $result->close();
        }
        else{
            $returnResult = false;
        }
        
        return $returnResult;
    }
    
    
    
    
    
    public function getEmployeesList()
    {
        $sql = "SELECT * FROM employees JOIN dept_manager ON employees.emp_no = dept_manager.emp_no JOIN departments ON dept_manager.dept_no = departments.dept_no";
        
        // LEFT JOIN dept_emp ON employees.emp_no = dept_emp.emp_no
        
        $result = self::$conn->query($sql);
        
        $sql2 = "SELECT * FROM employees JOIN dept_emp ON employees.emp_no = dept_emp.emp_no JOIN departments ON dept_emp.dept_no = departments.dept_no";
        $result2 = self::$conn->query($sql2);
        
        if ( (isset($result) && $result) || (isset($result2) && $result2) ){

            $returnResult = []; // ou array()

            if(isset($result) && $result){
                while ($row = $result->fetch_assoc()){
                    // var_dump($row);
                    $role = "manager";                
                    $department = $row['dept_no'];
                    
                    $newEmployee = new Employee($row['first_name'], $row['last_name'], $row['birth_date'], $role, $department);
                    
                    $newEmployee->setGender($row['gender']);
                    $newEmployee->setCC($row['cc']);
                    $newEmployee->setEmployeeNumber($row['emp_no']);
                    $newEmployee->setHireDate($row['hire_date']);
                    $newEmployee->fromDate = $row['from_date'];
                    $newEmployee->toDate = $row['to_date'];

                    array_push($returnResult, $newEmployee);
                }
                $result->close();
            }

            if(isset($result2) && $result2){
                while ($row = $result2->fetch_assoc()){
                    // var_dump($row);
                    $role = "employee";                
                    $department = $row['dept_no'];
                                        
                    $newEmployee = new Employee($row['first_name'], $row['last_name'], $row['birth_date'], $role, $department);
                   
                    $newEmployee->setGender($row['gender']);
                    $newEmployee->setCC($row['cc']);
                    $newEmployee->setEmployeeNumber($row['emp_no']);
                    $newEmployee->setHireDate($row['hire_date']);
                    $newEmployee->fromDate = $row['from_date'];
                    $newEmployee->toDate = $row['to_date'];

                    array_push($returnResult, $newEmployee);
                }
                $result2->close();
            }


        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
        
    } // end getEmployeesList()


    public function getEmployee($employeeID){
        
        $sql = "SELECT * FROM employees JOIN dept_manager ON employees.emp_no = dept_manager.emp_no JOIN departments ON dept_manager.dept_no = departments.dept_no WHERE employees.emp_no = '".$employeeID."' ";
        
        $result = self::$conn->query($sql);
        
        // var_dump($result);
        $sql2 = "SELECT * FROM employees JOIN dept_emp ON employees.emp_no = dept_emp.emp_no JOIN departments ON dept_emp.dept_no = departments.dept_no WHERE employees.emp_no = '".$employeeID."' ";

        $result2 = self::$conn->query($sql2);

        $returnResult = []; // ou array()

        if ( (isset($result) && $result) || (isset($result2) && $result2) ){

           

            if(isset($result) && $result){
                while ($row = $result->fetch_assoc()){
                    // var_dump($row);
                    $role = "manager";                
                    $department = $row['dept_no'];
                    
                    $newEmployee = new Employee($row['first_name'], $row['last_name'], $row['birth_date'], $role, $department);
                    
                    $newEmployee->setGender($row['gender']);
                    $newEmployee->setCC($row['cc']);
                    $newEmployee->setEmployeeNumber($row['emp_no']);
                    $newEmployee->setHireDate($row['hire_date']);
                    $newEmployee->fromDate = $row['from_date'];
                    $newEmployee->toDate = $row['to_date'];

                    array_push($returnResult, $newEmployee);
                }
                $result->close();
            }

            if(isset($result2) && $result2){
                while ($row = $result2->fetch_assoc()){
                    // var_dump($row);
                    $role = "employee";                
                    $department = $row['dept_no'];
                                        
                    $newEmployee = new Employee($row['first_name'], $row['last_name'], $row['birth_date'], $role, $department);
                   
                    $newEmployee->setGender($row['gender']);
                    $newEmployee->setCC($row['cc']);
                    $newEmployee->setEmployeeNumber($row['emp_no']);
                    $newEmployee->setHireDate($row['hire_date']);
                    $newEmployee->fromDate = $row['from_date'];
                    $newEmployee->toDate = $row['to_date'];

                    array_push($returnResult, $newEmployee);
                }
                $result2->close();
            }


        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
        
    } // end getEmployee()
    
    
    
    
    
    public function getEmployeeSalaries($employeeID)
    {
        $sql = "SELECT * FROM salaries
        WHERE salaries.emp_no  = '".$employeeID."' ";
        
        $result = self::$conn->query($sql);
        
        // var_dump($result);
      
        $returnResult = []; // ou array()

        if(isset($result) && $result){

            while ($row = $result->fetch_assoc()){
                // var_dump($row);
              
                $empNo = $row['emp_no'];
                $salary = $row['salary'];
                $fromDate = $row['from_date'];
                $toDate = $row['to_date'];
                               
                $newSalary = new Salary($empNo, $salary, $fromDate, $toDate);
                
                array_push($returnResult, $newSalary);
            }
            $result->close();
        }
        else{
            echo "Error: ". self::$conn->error;
        }
        
        return $returnResult;
        
    } // end getEmployeeSalaries()

    
    
    public function addSalary(Salary $salaryObj){
        
        if ( isset($salaryObj) && !empty($salaryObj) ) {
            
            $sql = "INSERT INTO salaries (emp_no, salary, from_date, to_date) VALUES 
            ('".$salaryObj->empNo."', 
            '".$salaryObj->salary."',
            '".$salaryObj->fromDate."',
            '".$salaryObj->toDate."'
            )";
            
            
            if (self::$conn->query($sql) === TRUE) {
                // echo "Department has been added to the database.";
                return true;
            } else {
                return false;
            }      
            return false;
        }
        return false;
    }//end addSalary()
    
}//end class

