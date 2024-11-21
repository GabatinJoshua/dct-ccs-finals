<?php 
    session_start();
    $_SESSION['CURR_PAGE'] = 'student';
    require_once('../partials/header.php');
    require_once('../partials/side-bar.php');
    guard();


 ?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

</body>
</html>

<?php require_once ('../partials/footer.php');?>