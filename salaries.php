<?PHP 

session_start();

if ( (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) )
{
  
  ?>
  
  
  <!DOCTYPE html>
  <html lang="en">
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HR - Employee list</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .error {color: #FF0000;}
  </style>
  
  </head>
  <body>
  <h1>Employee list</h1>
  
  <ul>
    <li>
    <a href="listemployees.php">List and edit all employees + manage salaries.</a>
    </li>
    <li>
    <a href="index.php">Add an employee</a>
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
  $departmentNumberErr = $departmentNameErr = $firstNameErr = $lastNameErr = $genderErr = $hireDateErr = $birthDateErr = $departmentErr = $ccFileErr = $salaryErr = $fromDateErr =  $toDateErr = "";
  $departmentNumber = $departmentName = $firstName = $lastName = $gender = $hireDate = $birthDate = $department = $ccFile = $salary = $fromDate =  $toDate = "";
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

  function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }


  if (!empty($_GET['editemployee']) && $_GET['editemployee'] >= 0) {
    
    $editMode = true;
    $employeeID = sanitize_input( $_GET["editemployee"] );
    $arrayEmployee = $DB->getEmployee($employeeID);
    // var_dump($arrayEmployee);

     $name = $arrayEmployee[0]->getName();
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
  

    if (!empty($_POST["employeeID"])) $employeeID = sanitize_input( $_POST["employeeID"] );

    // Department Number and Name checks
    if (empty($_POST["salary"]) && isset($_POST["salary"]) && $_POST["salary"] <= 0) {
      $salaryErr = "Please insert a positive value for the salary.";
    } 
    else {
      $salary = sanitize_input( $_POST["salary"] );
     
      if (!preg_match("/^[0-9]+/", $salary)) {
        $salaryErr = "Only positive numbers are allowed.";
      }
      else $goodToGo++;
    }

    // From Date check
    if (empty($_POST["fromDate"])) {
      $fromDateErr = "Please add an initial date";
    } else {
      $fromDateSanitized = sanitize_input( $_POST["fromDate"] );
      
      $fromDateTimestamp = convertToTimestamp($fromDateSanitized);

      $fromDate = $fromDateSanitized;
      $goodToGo++;
    }

    // To Date check
    if (empty($_POST["toDate"])) {
      $toDateErr = "Please add an end date";
    } else {
      $toDateSanitized = sanitize_input( $_POST["toDate"] );
      
      $toDateTimestamp = convertToTimestamp($toDateSanitized);
      
      if ( $toDateTimestamp <= $fromDateTimestamp ) {
        $toDateErr = "\"To Date\" must be later than \"From Date\"";
      }
      else {
        $toDate = $toDateSanitized;
        $goodToGo++;
      }
    }
 
    
    if ( $goodToGo >=3 ){

      $newSalary = new Salary($employeeID, $salary, $fromDate, $toDate);
      
      
      $lastSalaryID = $DB->addSalary($newSalary);     

      if (empty($lastSalaryID) && $lastSalaryID !== "0")
      {
        Echo "<p>Error, Please try adding a new salary again ('From date' must be unique).</p>";
      }
      else{
        echo "<p style=\"color:red; text-align:center;\">Added a new salary!</p>";
      }
 
      
       
    }
    
    
  }
  
  
  
  ?>
  
  
  

<?PHP 

if ($listEmployeeSalaries = $DB->getEmployeeSalaries($employeeID)){

  echo '<div class="row" style="padding-top:50px;">
  <table style="width:90vw; border: 1px solid red;">
  <thead>
    <tr>
      <th>Name</th>
      <th>Salary</th>
      <th>From Date</th>
      <th>To Date</th>
    </tr>
  </thead>
  </tbody>
  ';

  foreach ($listEmployeeSalaries as $employeeSalary)
  {
    echo "<tr>";
    echo "<td>".$name."</td>";
    echo "<td>".$employeeSalary->salary." €</td>";
    echo "<td>".$employeeSalary->fromDate."</td>";
    echo "<td>".$employeeSalary->toDate."</td>";
    echo "</tr>";
  };
  
  echo "
  </tbody>
 </table>
  </div>";
}

?>
  
  <p class="error">* required</p>
  <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])."?editemployee=".$employeeID; ?>">  
  
  <div class="row">
  
  <label for="salary">Salary (in Euros):</label> <input id="salary" type="number" min="0" name="salary" required value="<?php echo $salary;?>">
  <span class="error">* <?php echo $salaryErr;?></span>
  <br><br>
  </div>

  <div class="row">
    <label for="fromDate">From date: </label> <input id="fromDate" type="date" name="fromDate" required value="<?php echo $fromDate;?>">
    <span class="error">* <?php echo $fromDateErr;?></span>
    <br><br>
    </div>
    
    <div class="row">
    <label for="toDate">To date: </label> <input id="toDate" type="date" name="toDate" required value="<?php echo $toDate;?>">
    <span class="error">* <?php echo $toDateErr;?></span>
    <br><br>
    </div>
    
    <?PHP 

    echo '   <input type="hidden" name="employeeID" value="'.$employeeID.'"> ';

    ?>

  <input type="submit" name="submit" value="Add salary">  
  </form>

  
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