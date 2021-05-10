<?PHP 

session_start();

// Verificar se Utilizador está na DB e autenticá-lo se password for correta e iniciar $_SESSION;
require_once('DBLogin.php');

function sanitize_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

if( strtolower( $_SERVER[ 'REQUEST_METHOD' ] ) == 'post' && !empty($_POST['username']) && !empty($_POST['password']) ){

  $DBLogin = new DBLogin();

  $username = sanitize_input( $_POST["username"]);
  $password = sanitize_input( $_POST["password"]);
  
  $md5Password = md5($password);

  $userAutenticado = $DBLogin -> verifyLogin($username, $md5Password);

  // echo "\$userAutenticado = ".$userAutenticado;

  if (!empty($userAutenticado) && $userAutenticado === true)
  {
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $username;

    // echo "<br>\$_SESSION['logged_in'] = " . $_SESSION['logged_in'];
    // echo "<br>\$_SESSION['username'] = " . $_SESSION['username'];
    // echo "<br>\$username = " . $username;
    // echo "<br>\$password = " . $password;
  }

  // session_destroy();
}






if ( (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) )
{
  require_once ( 'logged_in.php' );
}
else{

  $passwordErr = $usernameErr = "";
  $username = $password = "";
  

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
<link rel="stylesheet" href="style.css">
<style>
    .error {color: #FF0000;}
</style>

</head>
<body>
<h1>FAÇA LOGIN</h1>



<form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">  

  <div class="row">
    
      <label for="username">Nome de utilizador:</label> <input id="username" type="text" name="username" required value="<?php echo $username;?>">
      <span class="error">* <?php echo $usernameErr;?></span>
      <br><br>
  </div>

  <div class="row">
  <label for="password"> Password: </label> <input id="password" type="password" name="password" required>
    <span class="error">* <?php echo $passwordErr;?></span>
    <br><br>
  </div>

  <input type="submit" name="submit" value="Autenticar">  
</form>

</body>
</html>

<?PHP

};

?>