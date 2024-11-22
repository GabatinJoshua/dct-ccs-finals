<?php
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

// Check if subject_id and student_id are provided
if (isset($_GET['subject_id']) && isset($_SESSION['k'])) {
    $subjectId = $_GET['subject_id'];
    $studentId = $_SESSION['k'];

    // Open the database connection
    $con = openConnection();

    // SQL query to fetch the student's details for the specified subject
    $strSql = "
        SELECT 
            s.student_id, 
            s.first_name, 
            s.last_name, 
            sub.subject_code, 
            sub.subject_name
        FROM 
            students_subjects ss
        LEFT JOIN students s ON ss.student_id = s.student_id
        LEFT JOIN subjects sub ON ss.subject_id = sub.id
        WHERE 
            ss.student_id = ? AND ss.subject_id = ?
    ";

    if ($stmt = mysqli_prepare($con, $strSql)) {
        mysqli_stmt_bind_param($stmt, "ii", $studentId, $subjectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Extract student details
            $studentId = $row['student_id'];
            $firstName = $row['first_name'];
            $lastName = $row['last_name'];
            $subjectCode = $row['subject_code'];
            $subjectName = $row['subject_name'];
        } else {
            echo '<div class="alert alert-danger">Student or subject not found.</div>';
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<div class="alert alert-danger">Error preparing SQL query.</div>';
        exit;
    }

    // Handling form submission for detaching subject
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnDelete'])) {
        // SQL query to detach the subject from the student
        $detachSubjectSql = "DELETE FROM students_subjects WHERE student_id = ? AND subject_id = ?";
        
        if ($stmtDetach = mysqli_prepare($con, $detachSubjectSql)) {
            mysqli_stmt_bind_param($stmtDetach, "ii", $studentId, $subjectId);
            if (mysqli_stmt_execute($stmtDetach)) {
                $_SESSION['successMsg'] = 'Subject detached successfully!';
                header('Location: attach-subject.php'); // Redirect to subject attachment page after success
                exit;
            } else {
                echo '<div class="alert alert-danger">Error detaching the subject: ' . mysqli_error($con) . '</div>';
            }
            mysqli_stmt_close($stmtDetach);
        }
    }

    // Close the database connection
    closeConnection($con);
} else {
    echo '<div class="alert alert-danger">No student or subject specified.</div>';
    exit;
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Detach Subject from Student</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="attach-subject.php">Attach Subjects To Student</a></li>        
            <li class="breadcrumb-item">Detach Subject from Student</li>
        </ol>
    </nav>

    <form class="border p-4 rounded" method="POST">
        <?php if (isset($_SESSION['successMsg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['successMsg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['successMsg']); ?>
        <?php endif; ?>

        <p class="mb-3">Are you sure you want to detach the following subject from the student?</p>

        <ul class="mb-3">
            <li>
                <strong>Student ID:</strong> <?php echo htmlspecialchars($studentId); ?>
            </li>
            <li>
                <strong>Full Name:</strong> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?>
            </li>
            <li>
                <strong>Subject Code:</strong> <?php echo htmlspecialchars($subjectCode); ?>
            </li>
            <li>
                <strong>Subject Name:</strong> <?php echo htmlspecialchars($subjectName); ?>
            </li>
        </ul>

        <div class="d-flex">
            <button class="btn btn-secondary me-2" type="button" id="btnCancel" onclick="window.location.href='register.php'">Cancel</button>
            <button class="btn btn-primary" type="submit" name="btnDelete" id="btnDelete">Detach Subject Record</button>
        </div>
    </form>
</main>

<?php require_once('../partials/footer.php'); ?>
