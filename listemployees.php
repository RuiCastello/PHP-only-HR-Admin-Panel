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
  $departmentNumberErr = $departmentNameErr = $firstNameErr = $lastNameErr = $genderErr = $hireDateErr = $birthDateErr = $departmentErr = $ccFileErr = $salaryErr = "";
  $departmentNumber = $departmentName = $firstName = $lastName = $gender = $hireDate = $birthDate = $department = $ccFile = $salary = "";
  $goodToGo = 0;
  


  function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
  
    // Department Number and Name checks
    if (empty($_POST["departmentNumber"]) && isset($_POST["departmentNumber"]) && $_POST["departmentNumber"] !== "0") {
      $departmentNumberErr = "Please insert a department number.";
    } 
    else {
      $departmentNumber = sanitize_input( $_POST["departmentNumber"] );
     
      if (!preg_match("/^[0-9]+/", $departmentNumber)) {
        $departmentNumberErr = "Only positive numbers are allowed.";
      }
      else $goodToGo++;
    }

    if (empty($_POST["departmentName"])) {
      $departmentNameErr = "Please insert a department name.";
    } else {
      $departmentName = sanitize_input( $_POST["departmentName"] );
      // check if department name only contains letters and whitespace
      if (!preg_match("/^[a-zA-Z\u00C0-\u017F\s]+/",$departmentName)) {
        $departmentNameErr = "Only letters and whitespaces are allowed.";
      }
      else $goodToGo++;
    }
    
 
    
    if ( $goodToGo >=2 ){

      $newDepartment = new Department($departmentNumber, $departmentName);
      


      
      $lastDepartmentID = $DB->addDepartment($newDepartment);     

      if (empty($lastDepartmentID) && $lastDepartmentID !== "0")
      {
        Echo "<p>Error, Please try adding a new department again.</p>";
      }
      elseif ($lastDepartmentID == "already_exists")
      {
        Echo "<p>Error, a department with similar data already exists, please add a new and unique department (both the number and name must be unique).</p>";
      }
      else{
        echo "<p style=\"color:red; text-align:center;\">Added a new department!</p>";
      }
 
      
       
    }
    
    
  }
  
  
  
  ?>
  
  
  

<?PHP 

if ($listEmployees = $DB->getEmployeesList()){

  echo '<div class="row" style="padding-top:50px;">
  <table style="width:90vw;">
  <thead>
    <tr>
      <th>Name</th>
      <th>Birth Date</th>
      <th>Hire Date</th>
      <th>Department</th>
      <th>Role</th>
      <th>Gender</th>
      <th>CC</th>
      <th>From Date</th>
      <th>To Date</th>
      <th>Edit Profile</th>
      <th>Manage Salaries</th>
    </tr>
  </thead>
  </tbody>
  ';

  foreach ($listEmployees as $employeeObj)
  {
    echo "<tr>";
    echo "<td>".$employeeObj->getName()."</td>";
    echo "<td>".$employeeObj->getBirthDate()."</td>";
    echo "<td>".$employeeObj->getHireDate()."</td>";
    echo "<td>".$employeeObj->getDepartment()."</td>";
    echo "<td>".$employeeObj->getRole()."</td>";
    echo "<td>".$employeeObj->getGender()."</td>";
    echo "<td>".$employeeObj->getCC()."</td>";
    echo "<td>".$employeeObj->fromDate."</td>";
    echo "<td>".$employeeObj->toDate."</td>";
    echo "<td><a href=\"index.php?editemployee=".$employeeObj->getEmployeeNumber()."\">Edit</a></td>";
    echo "<td><a href=\"salaries.php?editemployee=".$employeeObj->getEmployeeNumber()."\">Salaries</a></td>";
    echo "</tr>";
  };
  
  echo "
  </tbody>
 </table>
  </div>";
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