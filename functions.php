<?php    
    DEFINE("DB_SERVER", "localhost");
    DEFINE("DB_USERNAME", "root");
    DEFINE("DB_PASSOWORD", "");
    DEFINE("DB_NAME", "dct-ccs-finals");


    function getUsers($email, $password){
        $con = openConnection();

        $strSql = "
                    SELECT * FROM users
                    WHERE email = '$email'
                    AND password = '$password'

            ";

        if ($rsLogin = mysqli_query($con, $strSql)) {
            if(mysqli_num_rows($rsLogin) > 0){
                echo 'WLCOM';
                mysqli_free_result($rsLogin);
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