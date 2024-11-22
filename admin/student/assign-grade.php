<?php
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

// Ensure session is started and student_id is available
if (!isset($_SESSION['k'])) {
    echo 'Student ID not found in session.';
    exit; // Prevent further execution if no student_id is available
}

// Ensure student_id and subject_id are provided via GET and session
if (isset($_GET['subject_id']) && isset($_SESSION['k'])) {
    $subjectId = $_GET['subject_id'];
    $studentId = $_SESSION['k'];

    // Check if the studentId is valid
    if (empty($studentId)) {
        echo 'Invalid student ID';
        exit;
    }
} else {
    echo 'No valid student or subject provided.';
    exit;
}

// Open the database connection
$con = openConnection();

// SQL query to fetch student and subject details
$strSql = "
    SELECT 
        ss.student_id, 
        s.first_name, 
        s.last_name, 
        sub.subject_code, 
        sub.subject_name,
        ss.grade
    FROM 
        students_subjects ss
    LEFT JOIN students s ON ss.student_id = s.student_id
    LEFT JOIN subjects sub ON ss.subject_id = sub.id
    WHERE 
        ss.student_id = ? AND sub.id = ?
";

if ($stmt = mysqli_prepare($con, $strSql)) {
    mysqli_stmt_bind_param($stmt, "ii", $studentId, $subjectId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        // Extract the data from the query result
        $studentId = $row['student_id'];
        $firstName = $row['first_name'];
        $lastName = $row['last_name'];
        $subjectCode = $row['subject_code'];
        $subjectName = $row['subject_name'];
        $currentGrade = $row['grade']; // Retrieve current grade
    } else {
        echo '<div class="alert alert-danger">No student or subject found.</div>';
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    echo '<div class="alert alert-danger">Error preparing SQL query.</div>';
    exit;
}

// Handling form submission for grade update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the new grade from the form input
    $grade = $_POST['grade'];

    // Check if the grade is not empty
    if (!empty($grade)) {
        // Check if the student-subject relationship exists
        $checkSql = "SELECT * FROM students_subjects WHERE student_id = ? AND subject_id = ?";
        
        if ($stmt = mysqli_prepare($con, $checkSql)) {
            mysqli_stmt_bind_param($stmt, "ii", $studentId, $subjectId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                // If the record exists, update the grade
                $updateGradeSql = "UPDATE students_subjects SET grade = ? WHERE student_id = ? AND subject_id = ?";
                if ($stmtUpdate = mysqli_prepare($con, $updateGradeSql)) {
                    mysqli_stmt_bind_param($stmtUpdate, "sii", $grade, $studentId, $subjectId);
                    if (mysqli_stmt_execute($stmtUpdate)) {
                        $_SESSION['successMsg'] = 'Grade updated successfully!';
                    } else {
                        echo '<div class="alert alert-danger">Error updating the grade: ' . mysqli_error($con) . '</div>';
                    }
                    mysqli_stmt_close($stmtUpdate);
                }
            } else {
                // If no record exists, insert a new one
                $insertGradeSql = "INSERT INTO students_subjects (student_id, subject_id, grade) VALUES (?, ?, ?)";
                if ($stmtInsert = mysqli_prepare($con, $insertGradeSql)) {
                    mysqli_stmt_bind_param($stmtInsert, "iis", $studentId, $subjectId, $grade);
                    if (mysqli_stmt_execute($stmtInsert)) {
                        $_SESSION['successMsg'] = 'Grade assigned successfully!';
                    } else {
                        echo '<div class="alert alert-danger">Error inserting the grade: ' . mysqli_error($con) . '</div>';
                    }
                    mysqli_stmt_close($stmtInsert);
                }
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        echo '<div class="alert alert-danger">Grade cannot be empty.</div>';
    }
}

// Close the database connection
closeConnection($con);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Assign Grade for Student</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="attach-subject.php">Attach Subjects To Student</a></li>        
            <li class="breadcrumb-item">Assign Grade to Subject</li>
        </ol>
    </nav>

    <!-- Success Message -->
    <?php if (isset($_SESSION['successMsg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['successMsg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['successMsg']); // Clear success message after displaying ?>
    <?php endif; ?>

    <form method="POST" class="border p-4 rounded">
        <h4>Selected Student Information</h4>
        <ul>
            <li><strong>Student ID:</strong> <?php echo htmlspecialchars($studentId); ?></li>
            <li><strong>Full Name:</strong> <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></li>
            <li><strong>Subject Code:</strong> <?php echo htmlspecialchars($subjectCode); ?></li>
            <li><strong>Subject Name:</strong> <?php echo htmlspecialchars($subjectName); ?></li>
        </ul>

        <hr>

        <div class="mb-3">
            <label for="grade" class="form-label">Grade</label>
            <input type="text" class="form-control" id="grade" name="grade" placeholder="Enter grade" required value="<?php echo htmlspecialchars($currentGrade ?? ''); ?>"> <!-- Pre-fill the input with the current grade -->
        </div>

        <button type="submit" class="btn btn-primary">Assign Grade</button>
    </form>
</main>

<?php require_once('../partials/footer.php'); ?>
