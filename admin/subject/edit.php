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
    $strSql = "SELECT * FROM subjects WHERE id = " . $_SESSION['k'];

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

    // Initialize error message array
    $err = [];

    // Add/Update subject logic
    if (isset($_POST['btnAdd'])) {
        // Open the connection
        $con = openConnection();

        // Sanitize input data
        $subjectCode = sanitizeInput($con, $_POST['txtSubjectCode']);
        $subjectName = sanitizeInput($con, $_POST['txtSubjectName']);

        // Validate inputs
        if (empty($subjectCode)) {
            $err[] = 'Subject Code is Required!';
        }
        if (empty($subjectName)) {
            $err[] = 'Subject Name is Required!';
        }

        // Since we want to allow updating even with the same subject code, skip the check for duplicate subject code
        if (empty($err)) {
            $strSql = "UPDATE subjects SET subject_code = ?, subject_name = ? WHERE id = ?";

            if ($stmt = mysqli_prepare($con, $strSql)) {
                mysqli_stmt_bind_param($stmt, "ssi", $subjectCode, $subjectName, $_SESSION['k']);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    // Redirect to add.php after successful update
                    header("Location: add.php");
                    exit;
                } else {
                    $err[] = "Error: Could not update subject.";
                }

                mysqli_stmt_close($stmt);
            }
        }

        // Close the database connection
        closeConnection($con);
    }

    // Check for success message in session
    $successMsg = isset($_SESSION['successMsg']) ? $_SESSION['successMsg'] : '';
    unset($_SESSION['successMsg']); // Clear the success message after displaying it
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Edit Subject</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'subject' ? 'active' : ''); ?>"><a href="add.php">Add Subject</a></li>
            <li class="breadcrumb-item">Edit Subject</li>
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

        <div class="form-group mb-3"> 
            <input type="text" class="form-control" maxlength="4" value="<?php echo (isset($recPersons['subject_code']) ? htmlspecialchars($recPersons['subject_code']) : ''); ?>" id="txtSubjectCode" name="txtSubjectCode" placeholder="Subject Code">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" value="<?php echo (isset($recPersons['subject_name']) ? htmlspecialchars($recPersons['subject_name']) : ''); ?>" id="txtSubjectName" name="txtSubjectName" placeholder="Subject Name">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100" name="btnAdd" id="btnAdd">Update Subject</button>
        </div>
    </form>
</main>

<?php require_once('../partials/footer.php'); ?>
