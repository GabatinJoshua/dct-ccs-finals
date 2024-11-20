<?php    
    DEFINE("DB_SERVER", "localhost");
    DEFINE("DB_USERNAME", "root");
    DEFINE("DB_PASSOWORD", "");
    DEFINE("DB_NAME", "dct-ccs-finals");

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



    





     

    
?>