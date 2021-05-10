<?PHP 

if ( (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) )
{
  
  ?>
  
  
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR - Employee Administration Form</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .error {color: #FF0000;}
  </style>
  
  </head>
  <body>
  <h1>Employee data management</h1>
  
  <ul>
  <li>
  <a href="listemployees.php">List and edit all employees + manage salaries.</a>
  </li>
  <li>
  <a href="departmentform.php">Add a department</a>
  </li>
  </ul>
  
  <?php
  
  require_once ( 'main.php' );
  
  
  // Faz override à configuração do php.ini para mostrar todos os warnings.
  // error_reporting(-1);
  // ini_set('display_errors', 'On');
  
  // define variables and set to empty values
  $firstNameErr = $lastNameErr = $genderErr = $hireDateErr = $birthDateErr = $departmentErr = $ccFileErr = $salaryErr = $roleErr = "";
  $firstName = $lastName = $gender = $hireDate = $birthDate = $department = $ccFile = $salary = $role = "";
  $goodToGo = 0;
  
  function convertToTimestamp($date){
    
    $timestamp = false;
    $newDate = DateTime::createFromFormat('Y-m-d', $date);
    
    if ($newDate !== false) {
      $timestamp = $newDate->getTimestamp();
      // echo "<br>".$newDate->getTimestamp();
      // echo "<br>".date('d/m/Y', $newDate->getTimestamp());
    }
    
    return $timestamp;
  }
  
  
  if ($listaDepartamentos = $DB->getDepartmentsList()) $departmentsExist = true;
  else $departmentsExist = false;
  
  $editMode = false;

  if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['editemployee']) && $_GET['editemployee'] >= 0) {
    
    $editMode = true;
    $employeeID = sanitize_input( $_GET["editemployee"] );
    $arrayEmployee = $DB->getEmployee($employeeID);
    // var_dump($arrayEmployee);


     $firstName = $arrayEmployee[0]->getFirstName();
     $lastName = $arrayEmployee[0]->getLastName();
     $gender = $arrayEmployee[0]->getGender();
     $hireDate = $arrayEmployee[0]->getHireDate();
     $birthDate = $arrayEmployee[0]->getBirthDate();
     $department = $arrayEmployee[0]->getDepartment();
     $ccFile = $arrayEmployee[0]->getCC();
     $role = $arrayEmployee[0]->getRole();
  }
  
  
  
  
  if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_GET['logout']) && $_GET['logout'] == 'go') {
    unset($_SESSION["logged_in"]);
    unset($_SESSION["username"]);
    unset($_SESSION["password"]);
    session_destroy();
    echo '<div class="modal"><p>Logging out...</p></div>';
    header('Refresh: 2; URL = index.php');
  }
  elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST['username']) ) {
    
    // First Name and Last Name checks
    if (empty($_POST["firstName"])) {
      $firstNameErr = "Please insert a first name.";
    } else {
      $firstName = sanitize_input( $_POST["firstName"] );
      // check if name only contains letters and whitespace
      if(strlen($firstName) <= 1){
        $firstNameErr = "First name should be longer than one character.";
      }
      elseif (!preg_match("/^[a-zA-Z\u00C0-\u017F\s]+/", $firstName)) {
        $firstNameErr = "Only letters and whitespaces are allowed.";
      }
      else $goodToGo++;
    }
    if (empty($_POST["lastName"])) {
      $nameErr = "Please insert a last name.";
    } else {
      $lastName = sanitize_input( $_POST["lastName"] );
      // check if name only contains letters and whitespace
      if(strlen($lastName) <= 1){
        $lastNameErr = "Last name should be longer than one character.";
      }
      elseif (!preg_match("/^[a-zA-Z\u00C0-\u017F\s]+/",$lastName)) {
        $lastNameErr = "Only letters and whitespaces are allowed.";
      }
      else $goodToGo++;
    }
    
    //Birth Date check
    if (empty($_POST["birthDate"])) {
      $birthDateErr = "Please add a birth date";
    } else {
      $birthDateSanitized = sanitize_input( $_POST["birthDate"] );
      
      $birthDateTimestamp = convertToTimestamp($birthDateSanitized);
      
      if ( $birthDateTimestamp >= strtotime("-18 years") ) {
        $birthDateErr = "Too young. Needs to be 18+ to work here.";
      }
      else {
        $birthDate = $birthDateSanitized;
        $goodToGo++;
      }
    }
    
    //Hire Date check
    if (empty($_POST["hireDate"])) {
      $hireDateErr = "Please add a hire date";
    } else {
      $hireDate = sanitize_input( $_POST["hireDate"] );
      
      $goodToGo++;
    }
    
    
    if (empty($_POST["department"])) {
      $departmentErr = "Please select a department";
    } 
    else {
      $department = sanitize_input( $_POST["department"]);
      // TO DO
      $departmentObjPost = new Department($department);
      if ( $DB->checkDepartmentExists($departmentObjPost) == false ) {
        $departmentErr = "Department doesn't exist, please select a valid department.";
      }
      else $goodToGo++;
    }
    
    if (!empty($_POST["role"])){
      $role = sanitize_input( $_POST["role"]);
      // TO DO
      if ( $role == "employee" || $role == "manager" ) {
        $goodToGo++;
      }
      else $roleErr = "Please select a valid role.";
    }
    
    
    if (!empty($_POST["gender"])){
      $gender = sanitize_input( $_POST["gender"]);
      // TO DO
      if ( $gender == "M" || $gender == "F" ) {
        // $goodToGo++;
      }
      else $genderErr = "Please select a valid gender.";
    }
    
    
    
    if (!empty($_POST["salary"])) $salary = sanitize_input( $_POST["salary"] );
    if (!empty($_POST["employeeID"])) $employeeID = sanitize_input( $_POST["employeeID"] );

    $editSent = false;
    if (!empty($_POST["editsent"]) && $_POST["editsent"] == true) $editSent = true;
    
    
    
    
    
    if ( $goodToGo >=6 ){
      
      // $birthDateTimestamp = convertToTimestamp($birthDate);
      
      $newEmployee = new Employee($firstName, $lastName, $birthDate, $role, $department);
      
      $birthDatePT = date('d-m-Y', convertToTimestamp($birthDate));

      if (!$editSent && $DB->checkEmployeeExists($newEmployee) == true){
        echo "<p style=\"color:red; text-align:center;\">The employee ".ucfirst($firstName)." ".ucfirst($lastName)." born on $birthDatePT, already exists in the database. <br>Please insert a different employee.</p>";
      }
      else{
        
        
        if (!empty($hireDate)) $newEmployee ->setHireDate($hireDate);
        if (!empty($gender)) $newEmployee ->setGender($gender);
        
        // echo "<pre> Dump da variável \$newEmployee : ";
        // var_dump($newEmployee);
        // echo "</pre>";
        echo "<p style=\"color:red; text-align:center;\">Added a new employee!</p>";
        
        
        if ($editSent && !empty($employeeID) ){

          $employeeId = $employeeID;
          $arrayEmployee = $DB->getEmployee($employeeID);
          // var_dump($arrayEmployee);

          $newEmployee->fromDate = $arrayEmployee[0]->fromDate;
          $newEmployee->toDate = $arrayEmployee[0]->toDate;
          $newEmployee->setEmployeeNumber( $arrayEmployee[0]->getEmployeeNumber() );
          $newEmployee->setCC( $arrayEmployee[0]->getCC() );


          $DB->editEmployee($newEmployee);
        }
        else{
          // Adiciona um empregado e devolve o last id
          $employeeId = $DB->addEmployee($newEmployee);
          if (isset($employeeId) && $employeeId >= 0) $newEmployee->setEmployeeNumber($employeeId);
        }
       
        
        
        
        // Para ver a estrutura de $_FILES["ccFile"], ela muda para um multilevel array se o input "name" tiver "[]" para multiplos ficheiros
        // var_dump($_FILES["ccFile"]);
        
        // Se algum ficheiro foi uploaded, então movêmo-lo para o directório correto
        if( strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) == 'post' && !empty( $_FILES ) && isset($employeeId) && $employeeId >= 0){
          foreach ($_FILES["ccFile"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
              $tmp_name = $_FILES["ccFile"]["tmp_name"][$key];
              // basename() may prevent filesystem traversal attacks;
              // further validation/sanitation of the filename may be appropriate
              $name = basename($_FILES["ccFile"]["name"][$key]);
              $extension = pathinfo($_FILES['ccFile']["name"][$key], PATHINFO_EXTENSION);
              $destinationPath = "data/".$employeeId.".".$extension;
              $fileWasUploadedSucessfully = move_uploaded_file($tmp_name, $destinationPath);
              
              // ADICIONA path para o ficheiro $ccFile na coluna "cc" do respectivo employee
              if ($fileWasUploadedSucessfully) {
                $newEmployee->setCC($destinationPath);
                // var_dump($newEmployee);
                $DB->editEmployee($newEmployee);
              }
            }
          }
        }
        
      }
      
    }
    
    
    
    
  }
  
  
  if (empty($departmentsExist)){
    
    echo '<p> No departments have been created yet.<br> Please <a href="departmentform.php" >add a department</a> before adding a new employee to the database. </p>';
    
  }
  else{
    
    ?>
    
    
    
    <p class="error">* required</p>
    <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
    
    <div class="row">
    
    <label for="firstName">First name:</label> <input id="firstName" type="text" name="firstName" required value="<?php echo $firstName;?>">
    <span class="error">* <?php echo $firstNameErr;?></span>
    <br><br>
    </div>
    
    <div class="row">
    <label for="lastName">Last name: </label> <input id="lastName" type="text" name="lastName" required value="<?php echo $lastName;?>">
    <span class="error">* <?php echo $lastNameErr;?></span>
    <br><br>
    </div>
    
    <div class="row">
    <label for="birthDate">Birth date: </label> <input id="birthDate" type="date" name="birthDate" required value="<?php echo $birthDate;?>">
    <span class="error">* <?php echo $birthDateErr;?></span>
    <br><br>
    </div>
    
    <div class="row">
    <label for="hireDate">Hire date: </label> <input id="hireDate" type="date" name="hireDate" required value="<?php echo $hireDate;?>">
    <span class="error">* <?php echo $hireDateErr;?></span>
    <br><br>
    </div>
    
    
    
    
    
    <?PHP 
    
    if ($departmentsExist){
      
      echo '<div class="row">
      <label for="department">Department: </label> 
      <div class="second-column">
      <select name="department" id="department" required>
      ';
      
      foreach ($listaDepartamentos as $DepartamentoObj)
      {
        $selected = "";
        if (!empty($department) && $DepartamentoObj->departmentNumber == $department) $selected = "selected";

        echo "<option $selected value=\"".$DepartamentoObj->departmentNumber."\">".$DepartamentoObj->departmentName."</option>";
      };
      
      echo "
      </select>
      <span class=\"error\">* $departmentErr</span>
      <br><br>
      </div>
      <a href=\"departmentform.php\"> Add department?</a>
      </div>";
    }
    
    ?>
    
    
    
    <div class="row">
    <label for="role"> Role:</label>
    <div class="second-column">
    <input type="radio" name="role" id="role" required <?php if (isset($role) && $role =="employee") echo "checked";?> value="employee">Employee
    <input type="radio" name="role" <?php if (isset($role) && $role =="manager") echo "checked";?> value="manager">Manager
    <span class="error">* <?php echo $roleErr;?></span>
    <br><br>
    </div>
    </div>
    
    
    <!-- Salary se calhar devia ser à parte, REVER ISTO -->
    <!-- <div class="row">
    <label for="salary">Salary: </label> <input type="text" id="salary" name="salary" value="<?php /* echo $salary; */ ?>">
    <span class="error"><?php echo $salaryErr;?></span>
    <br><br>
    </div> -->
    
    <div class="row">
    <label for="gender"> Gender:</label>
    <div class="second-column">
    <input type="radio" name="gender" id="gender" <?php if (isset($gender) && $gender =="M") echo "checked";?> value="M">Male
    <input type="radio" name="gender" <?php if (isset($gender) && $gender =="F") echo "checked";?> value="F">Female
    <span class="error"> <?php echo $genderErr;?></span>
    <br><br>
    </div>
    </div>
    
    
    <!-- Para enviar ficheiros é preciso acrescentar isto ao <form> enctype="multipart/form-data"  -->
    <div class="row">
    <label for="ccFile"> CC (pdf):</label>
    <div class="second-column">
    <!-- O attributo "name" tem de ter brackets "[]" no fim se quisermos ter multiplos ficheiros -->
    <input type="file" id="ccFile" name="ccFile[]" >
    <span class="error"> <?php echo $ccFileErr;?></span>
    <br><br>
    </div>
    </div>
    
    
    <?PHP
    
    if($editMode == true)
    {
      echo '   <input type="hidden" name="editsent" value="true"> ';
      echo '   <input type="hidden" name="employeeID" value="'.$employeeID.'"> ';
      echo '   <input type="submit" name="submit" value="Edit employee">  ';
    }else{
      echo '   <input type="submit" name="submit" value="Add employee">    ';
    }

    ?>


   
    </form>
    
    <?PHP 
  }

  ?>
    <!-- Logout -->
    <div class="logout">
    <form method="POST" action="<?php echo (htmlspecialchars($_SERVER["PHP_SELF"])."?logout=go"); ?>">  
    
    <input id="logout" type="submit" name="logout_btn" value="Logout">
    </form>
    </div>
    
    
    </body>
    </html>
    
    
    <?PHP 
    
  
  
}
else{
  
  ?>
  
  <a href="index.php">Please login.</a>
  
  <?PHP 
  
}
?>