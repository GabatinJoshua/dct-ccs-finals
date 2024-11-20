<?php    
    DEFINE("DB_SERVER", "localhost");
    DEFINE("DB_USERNAME", "root");
    DEFINE("DB_PASSOWORD", "");
    DEFINE("DB_NAME", "dct-ccs-finals");

    function userHash($email, $password){
        $con = openConnection();

        $emailCheck = htmlspecialchars($email);
        $passwordCheck = htmlspecialchars($password);

        $emailCheck = stripcslashes($email);
        $passwordCheck = stripcslashes($password);

        $emailCheck = mysqli_real_escape_string($con, $emailCheck);
        $passwordCheck = mysqli_real_escape_string($con, $passwordCheck);

        $passwordCheck = md5($passwordCheck);

        return [$emailCheck, $passwordCheck];
    }


    function getUsers($email, $password){
        $con = openConnection();

        $strSql = "
                    SELECT * FROM users
                    WHERE email = '$email'
                    AND password = '$password'

            ";

        if ($rsLogin = mysqli_query($con, $strSql)) {
            if(mysqli_num_rows($rsLogin) > 0){
                header('Location: admin/dashboard.php');
                mysqli_free_result($rsLogin);
                exit();
            }
            else
                echo "no acc";
        }
        else
            echo "error";

        closeConnection($con);
    }


    function openConnection(){
        $con = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSOWORD,DB_NAME);

        if ($con == false) {
            die("ERROR: could not connect" . mysqli_connect_error());
        }
        return $con;
    }

    function closeConnection($con){
        mysqli_close($con);
    }

     

    
?>