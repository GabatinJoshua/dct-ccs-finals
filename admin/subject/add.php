<?php 
session_start();
$_SESSION['CURR_PAGE'] = 'subject';
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

// Variables to hold feedback
$successMessage = '';

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

    // If no errors, insert into the database
    if (empty($err)) {
        // Prepared statement to prevent SQL injection
        $strSql = "INSERT INTO subjects (subject_code, subject_name) VALUES (?, ?)";

        // Prepare the statement
        if ($stmt = mysqli_prepare($con, $strSql)) {
            // Bind the parameters
            mysqli_stmt_bind_param($stmt, "ss", $subjectCode, $subjectName);
            
            // Execute the statement
            mysqli_stmt_execute($stmt);
            
            // Check if the insert was successful
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $successMessage = "Subject added successfully!";
            } else {
                $errorMessage = "Error: Could not insert subject.";
            }
            
            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            $errorMessage = "Error preparing the query: " . mysqli_error($con);
        }
    } else {
        $errorMessage = implode('<br>', $err);
    }

    // Close the database connection
    closeConnection($con);
}

?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'subject' ? 'active' : ''); ?>">Add Subject</li>
        </ol>
    </nav>

    <form class="border p-4 rounded" method="POST">
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($successMessage); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($err) && is_array($err)): ?>
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
            <input type="text" class="form-control" id="txtSubjectCode" name="txtSubjectCode" placeholder="Subject Code">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" id="txtSubjectName" name="txtSubjectName" placeholder="Subject Name">
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

                if(!empty($recPersons)){
                    foreach ($recPersons as $key => $value) {
                        echo '<tr>';
                            echo '<td>' . htmlspecialchars($value['subject_code']) . '</td>'; 
                            echo '<td>' . htmlspecialchars($value['subject_name']) . '</td>';
                            echo '<td class="text-center">';
                                echo '<a href="edit.php" class="btn btn-success">Edit</a>';
                                echo '<a href="delete.php" class="btn btn-danger">Delete</a>';
                            echo '<td>';
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
