<?php    
    DEFINE("DB_SERVER", "localhost");
    DEFINE("DB_USERNAME", "root");
    DEFINE("DB_PASSWORD", "");
    DEFINE("DB_NAME", "dct-ccs-finals");

    function openConnection(){
        $con = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_NAME);

        if ($con == false) {
            die("ERROR: could not connect" . mysqli_connect_error());
        }
        return $con;
    }

    function closeConnection($con){
        mysqli_close($con);
    }

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


   function getUsers($email, $password) {
    $con = openConnection();

    $strSql = "
        SELECT * FROM users
        WHERE email = '$email'
        AND password = '$password'
    ";

    $userStatus = "";

    if ($rsLogin = mysqli_query($con, $strSql)) {
        if (mysqli_num_rows($rsLogin) > 0) {
            $userStatus = "success";
            mysqli_free_result($rsLogin);
        } else {
            // If no matching user, check if the email exists
            $strSqlEmailCheck = "SELECT * FROM users WHERE email = '$email'";
            $emailResult = mysqli_query($con, $strSqlEmailCheck);
            if (mysqli_num_rows($emailResult) > 0) {
                $userStatus = "password_incorrect";
            } else {
                $userStatus = "email_not_found";
            }
        }
    } else {
        echo "Error in query.";
    }

    closeConnection($con);

    return $userStatus;
}




  function checkError($email, $password) {
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } else {
        list($hashedEmail, $hashedPassword) = userHash($email, $password);

        $userStatus = getUsers($hashedEmail, $hashedPassword);

        if ($userStatus === "password_incorrect") {
            $errors[] = "Incorrect password. Please try again.";
        } elseif ($userStatus === "email_not_found") {
            $errors[] = "Account not found. Please check your credentials.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    return $errors;
}



    // In functions.php

    function guard() {
            if(!isset($_SESSION['auth']))
                header("location: ../index.php");
            }


    function countRecord($input){
    $con = openConnection();

    // Sanitize the table name input to prevent SQL injection
    $input = mysqli_real_escape_string($con, $input);

    $strSql = "
        SELECT COUNT(*) AS total FROM $input;
    ";

    if ($rsLogin = mysqli_query($con, $strSql)) {
        // Fetch the result as an associative array
        $row = mysqli_fetch_assoc($rsLogin);

        // Check if a result is returned
        if ($row) {
            $total = $row['total']; // Get the count value
            echo $total;
        } else {
            echo "Error fetching data.";
        }

        mysqli_free_result($rsLogin); // Free the result set
    } else {
        echo "Error in query.";
    }

    closeConnection($con);
}

    function getRecord($con, $strSql){
        $arrRec = [];
        $i = 0;

        if($rs = mysqli_query($con, $strSql)){
            if(mysqli_num_rows($rs) == 1){
                $rec = mysqli_fetch_array($rs);
                foreach ($rec as $key => $value) {
                    $arrRec = [$key] = $value;
                }
            }else if(mysqli_num_rows($rs) > 1){
                while ($rec = mysqli_fetch_array($rs)) {
                    foreach ($rec as $key => $value) {
                        $arrRec[$i][$key] = $value;
                    }
                    $i++;
                }
                mysqli_free_result($rs);
        }
        }else
            die("ERROR");
        
        return $arrRec;

    }


    function idQuery($con, $strSql){
         if(mysqli_query($con, $strSql))
            return mysqli_insert_id($con);
        else
            return 0;

    }

    function sanitizeInput($con, $input){
        return mysqli_real_escape_string($con, stripcslashes(htmlspecialchars($input)));
    }

    





     

    
?>