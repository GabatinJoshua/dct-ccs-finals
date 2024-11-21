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

    // Initialize error message array
    $err = [];

    // Add/Update student logic
    if (isset($_POST['btnAdd'])) {
        // Open the connection
        $con = openConnection();

        // Sanitize input data
        $studentID = sanitizeInput($con, $_POST['txtStudentID']);
        $firstName = sanitizeInput($con, $_POST['txtFName']);
        $lastName = sanitizeInput($con, $_POST['txtLName']);

        // Initialize error array
        $err = [];

        // Validate inputs
        if (empty($studentID)) {
            $err[] = 'Student ID is Required!';
        }
        if (empty($firstName)) {
            $err[] = 'First Name is Required!';
        }
        if (empty($lastName)) {
            $err[] = 'Last Name is Required!';
        }

        // Skip the duplicate check if updating the same student ID
        if (empty($err)) {
            // Check if the student ID is already in use, but skip if it's the current student's ID
            $checkDuplicateSql = "SELECT * FROM students WHERE student_id = ? AND id != ?";
            if ($stmt = mysqli_prepare($con, $checkDuplicateSql)) {
                mysqli_stmt_bind_param($stmt, "si", $studentID, $_SESSION['k']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) > 0) {
                    // If duplicate found, add error
                    $err[] = 'Student ID already exists!';
                }
                mysqli_stmt_close($stmt);
            } else {
                $err[] = "Error preparing the query: " . mysqli_error($con);
            }
        }

        // If no errors, update the student record
        if (empty($err)) {
            $strSql = "UPDATE students SET student_id = ?, first_name = ?, last_name = ? WHERE id = ?";

            if ($stmt = mysqli_prepare($con, $strSql)) {
                mysqli_stmt_bind_param($stmt, "sssi", $studentID, $firstName, $lastName, $_SESSION['k']);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    // Redirect to register.php after successful update
                    header("Location: register.php");
                    exit; // Ensure the page is refreshed after redirect
                } else {
                    $err[] = "Error: Could not update student.";
                }

                mysqli_stmt_close($stmt);
            } else {
                $err[] = "Error preparing the query: " . mysqli_error($con);
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
    <h1 class="h3 fw-normal">Edit Student</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item">Edit Student</li>
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
            <input type="text" class="form-control" maxlength="4" value="<?php echo (isset($recPersons['student_id']) ? htmlspecialchars($recPersons['student_id']) : ''); ?>" id="txtStudentID" name="txtStudentID" placeholder="Student ID">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" value="<?php echo (isset($recPersons['first_name']) ? htmlspecialchars($recPersons['first_name']) : ''); ?>" id="txtFName" name="txtFName" placeholder="First Name">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" value="<?php echo (isset($recPersons['last_name']) ? htmlspecialchars($recPersons['last_name']) : ''); ?>" id="txtLName" name="txtLName" placeholder="Last Name">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100" name="btnAdd" id="btnAdd">Update Student</button>
        </div>
    </form>
</main>

<?php require_once('../partials/footer.php'); ?>
