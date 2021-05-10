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
  <title>HR - Department Administration Form</title>
  <link rel="stylesheet" href="style.css">
  <style>
  .error {color: #FF0000;}
  </style>
  
  </head>
  <body>
  <h1>Department data management</h1>
  
  <ul>
    <li>
    <a href="listemployees.php">List and edit all employees + manage salaries.</a>
    </li>
    <li>
    <a href="index.php">Add an employee</a>
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
  
  
  
  <p class="error">* required</p>
  <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  
  
  <div class="row">
  
  <label for="departmentNumber">Department number:</label> <input id="departmentNumber" type="text" name="departmentNumber" required value="<?php echo $departmentNumber;?>">
  <span class="error">* <?php echo $departmentNumberErr;?></span>
  <br><br>
  </div>

  <div class="row">
  <label for="departmentName">Department name:</label> <input id="departmentName" type="text" name="departmentName" required value="<?php echo $departmentName;?>">
  <span class="error">* <?php echo $departmentNameErr;?></span>
  <br><br>
  </div>
  

  <input type="submit" name="submit" value="Add department">  
    
<?PHP 

if ($listaDepartamentos = $DB->getDepartmentsList()){

  echo '<div class="row" style="padding-top:50px;">
  <label for="department">Current Departments: </label> 
  <div class="second-column">
    <select name="department" id="department">
  ';

  foreach ($listaDepartamentos as $DepartamentoObj)
  {
    echo "<option value=\"".$DepartamentoObj->departmentNumber."\">".$DepartamentoObj->departmentName."</option>";
  };
  
  echo "
  </select>
  <span class=\"error\">$departmentErr</span>
  <br><br>
  </div>
  </div>";
}

?>
  
  

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