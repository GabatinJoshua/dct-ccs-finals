<?php 
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

if (isset($_GET['k'])) {
    $_SESSION['k'] = $_GET['k'];
}

// Open the connection
$con = openConnection();
$strSql = "SELECT * FROM students WHERE id = " . $_SESSION['k'];

// Execute query
if ($rsPerson = mysqli_query($con, $strSql)) {
    if (mysqli_num_rows($rsPerson) > 0) {
        // Fetch the result into $recPersons
        $recPersons = mysqli_fetch_array($rsPerson);
        mysqli_free_result($rsPerson);
    } else {
        echo '<tr>';
        echo '<td colspan="4" align="center">No Record Found!</td>';
        echo '</tr>';
    }
}

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Attach Subject To Student</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item">Attach Subject To Student</li>
        </ol>
    </nav>

    <form class="border p-4 rounded" method="POST">
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>SYSTEM ERROR</strong>
                <ul>
                    <?php foreach ($err as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <h1 class="h5 fw-normal">Select Student Information</h1>

        <ul class="mb-3">
            <li>
                <strong>Student ID:</strong> <?php echo htmlspecialchars($recPersons['student_id']); ?>
            </li>
            <li>
                <strong>Full Name:</strong> <?php echo htmlspecialchars($recPersons['first_name']) . ' ' . htmlspecialchars($recPersons['last_name']); ?>
            </li>
           
        </ul>

        	<hr>

        <div class="d-flex">
            
            <button class="btn btn-primary" type="submit" name="btnAttach" id="btnAttach">Attach Subject</button>
        </div>
    </form>

</main>

<?php require_once('../partials/footer.php'); ?>
