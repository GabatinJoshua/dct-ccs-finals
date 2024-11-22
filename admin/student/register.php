<?php 
session_start();
$_SESSION['CURR_PAGE'] = 'student';
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

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

    // Check for duplicate student ID
    if (empty($err)) {
        $checkDuplicateSql = "SELECT * FROM students WHERE student_id = ?";
        if ($stmt = mysqli_prepare($con, $checkDuplicateSql)) {
            mysqli_stmt_bind_param($stmt, "s", $studentID);
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

    // If no errors, insert into the database
    if (empty($err)) {
        $strSql = "INSERT INTO students (student_id, first_name, last_name) VALUES (?, ?, ?)";

        if ($stmt = mysqli_prepare($con, $strSql)) {
            mysqli_stmt_bind_param($stmt, "sss", $studentID, $firstName, $lastName);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Redirect back to the page to refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit; // Ensure the page is refreshed after redirect
            } else {
                $err[] = "Error: Could not insert student.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $err[] = "Error preparing the query: " . mysqli_error($con);
        }
    }

    // Close the database connection
    closeConnection($con);
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Register a New Student</h1><br>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>">Register Student</li>
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
            <input type="text" class="form-control" id="txtStudentID" name="txtStudentID" placeholder="Student ID" maxlength="4" value="<?php echo isset($studentID) ? htmlspecialchars($studentID) : ''; ?>">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" id="txtFName" name="txtFName" placeholder="First Name" value="<?php echo isset($firstName) ? htmlspecialchars($firstName) : ''; ?>">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" id="txtLName" name="txtLName" placeholder="Last Name" value="<?php echo isset($lastName) ? htmlspecialchars($lastName) : ''; ?>">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100" name="btnAdd" id="btnAdd">Add Student</button>
        </div>
    </form><br><br><br>

    <form class="border p-4 rounded">
        <table class="table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $con = openConnection();
                $strSql = "SELECT * FROM students ORDER BY student_id, first_name, last_name";
                $recPersons = getRecord($con, $strSql);

                if (!empty($recPersons)) {
                    foreach ($recPersons as $value) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($value['student_id']) . '</td>'; 
                        echo '<td>' . htmlspecialchars($value['first_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($value['last_name']) . '</td>';
                        echo '<td class="text-center">'; 
                            echo '<a href="edit.php?k=' . $value['id'] . '" class="btn btn-info me-2">Edit</a>';
                            echo '<a href="delete.php?k=' . $value['id'] . '" class="btn btn-danger me-2">Delete</a>';
                            echo '<a href="attach-subject.php?k=' . $value['id'] . '" class="btn btn-warning">Attach Subject</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3" class="text-center">No records found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </form>
</main>

<?php require_once('../partials/footer.php'); ?>
