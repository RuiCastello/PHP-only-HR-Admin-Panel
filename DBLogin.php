<?PHP 

require_once('DB.php');

class DBLogin extends DB{

    public function __construct(){
        
        parent::__construct();

    }


    public function verifyLogin($username, $password) {
        
        if ( isset($username) && !empty($username) && isset($password) && !empty($password) ) {
           
            $sql = "SELECT count(username) FROM utilizador 
            WHERE username = '".$username."' AND password = '".$password."'";
        }

            $result = parent::$conn->query($sql);
            // var_dump($result);

            if ( !empty($result) ) {
                
                $row = $result->fetch_assoc();
                
                if ($row['count(username)'] == 1)
                {
                    // echo "<br> Login encontrado <br>";
                    // print_r($row);
                    // echo "<br> \$row['count(username)'] = ". $row['count(username)'] ." <br>";
                    $result->close();
                    return true;
                }
                return false;

            } else {
                // echo "Error: " . $sql . "<br>" . self::$conn->error;
                return false;
            }
       

            return false;
    }//end verificarLogin()







}//end class

