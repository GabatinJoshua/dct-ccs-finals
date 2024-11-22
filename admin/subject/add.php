<?php
ob_start();  // Start output buffering

session_start();
$_SESSION['CURR_PAGE'] = 'subject';
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

if (isset($_POST['btnAdd'])) {
    // Open the connection
    $con = openConnection();

    // Sanitize input data
    $subjectCode = sanitizeInput($con, $_POST['txtSubjectCode']);
    $subjectName = sanitizeInput($con, $_POST['txtSubjectName']);

    // Initialize error array
    $err = [];

    // Validate inputs
    if (empty($subjectCode)) {
        $err[] = 'Subject Code is Required!';
    }
    if (empty($subjectName)) {
        $err[] = 'Subject Name is Required!';
    }

    // Check for duplicate subject code
    if (empty($err)) {
        $checkDuplicateSql = "SELECT * FROM subjects WHERE subject_code = ?";
        if ($stmt = mysqli_prepare($con, $checkDuplicateSql)) {
            mysqli_stmt_bind_param($stmt, "s", $subjectCode);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                // If duplicate found, add error
                $err[] = 'Subject Code already exists!';
            }
            mysqli_stmt_close($stmt);
        } else {
            $err[] = "Error preparing the query: " . mysqli_error($con);
        }
    }

    // If no errors, insert into the database
    if (empty($err)) {
        $strSql = "INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)";

        if ($stmt = mysqli_prepare($con, $strSql)) {
            mysqli_stmt_bind_param($stmt, "ss", $subjectCode, $subjectName);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                // Redirect back to the page to refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit; // Ensure the page is refreshed after redirect
            } else {
                $err[] = "Error: Could not insert subject.";
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
    <h1 class="h3 fw-normal">Add a New Subject</h1><br>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'subject' ? 'active' : ''); ?>">Add Subject</li>
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
    <!-- Retain the value if there's no error -->
		    <input type="text" class="form-control" 
		        id="txtSubjectCode" 
		        name="txtSubjectCode" 
		        placeholder="Subject Code" 
		        maxlength="4" 
		        value="<?php echo isset($_POST['txtSubjectCode']) ? htmlspecialchars($_POST['txtSubjectCode']) : ''; ?>">
		</div>
		<div class="form-group mb-3">
		    <!-- Retain the value if there's no error -->
		    <input type="text" class="form-control" 
		        id="txtSubjectName" 
		        name="txtSubjectName" 
		        placeholder="Subject Name"
		        value="<?php echo isset($_POST['txtSubjectName']) ? htmlspecialchars($_POST['txtSubjectName']) : ''; ?>">
		</div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100" name="btnAdd" id="btnAdd">Add Subject</button>
        </div>
    </form><br><br><br>

    <form class="border p-4 rounded">
        <table class="table">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $con = openConnection();
                $strSql = "SELECT * FROM subjects ORDER BY subject_code, subject_name";
                $recPersons = getRecord($con, $strSql);

                if (!empty($recPersons)) {
                    foreach ($recPersons as $value) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($value['subject_code']) . '</td>'; 
                        echo '<td>' . htmlspecialchars($value['subject_name']) . '</td>';
                        echo '<td class="text-center">';
                        	echo '<a href="edit.php?k=' . $value['id'] . '" class="btn btn-info me-2">Edit</a>';
                        	echo '<a href="delete.php?k=' . $value['id'] . '" class="btn btn-danger">Delete</a>';
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

<?php require_once ('../partials/footer.php');?> 

<?php ob_end_flush();  // Send the output buffer to the browser ?>
