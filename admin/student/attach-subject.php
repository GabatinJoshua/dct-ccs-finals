<?php 
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

if (isset($_GET['k'])) {
    $_SESSION['k'] = $_GET['k'];
}

// Open the connection (ensure it's open before running queries)
$con = openConnection();

// Query to get student information
$strSql = "SELECT * FROM students WHERE id = " . $_SESSION['k'];

// Execute query for student
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

// Query to get subjects data
$strSqlSubjects = "SELECT * FROM subjects";
$subjectsResult = mysqli_query($con, $strSqlSubjects);
if (mysqli_num_rows($subjectsResult) > 0) {
    $subjects = mysqli_fetch_all($subjectsResult, MYSQLI_ASSOC);  // Fetch all subjects as an associative array
} else {
    $subjects = [];
}

// Initialize error array
$err = [];

// Handle form submission to attach subject(s)
if (isset($_POST['btnAttach'])) {
    echo "Form submitted!"; // Debug message
    if (isset($_POST['subject_ids'])) {
        $subjectIds = $_POST['subject_ids'];  // Get the selected subject IDs as an array
        print_r($subjectIds); // Debug message to print selected subject IDs

        // Validate the input
        if (empty($subjectIds)) {
            $err[] = 'Please select at least one subject!';
        }

        // Attach the subjects to the student
        if (empty($err)) {
            foreach ($subjectIds as $subjectId) {
                $attachSubjectSql = "INSERT INTO students_subjects (student_id, subject_id) VALUES (?, ?)";

                if ($stmt = mysqli_prepare($con, $attachSubjectSql)) {
                    mysqli_stmt_bind_param($stmt, "ii", $_SESSION['k'], $subjectId);
                    if (mysqli_stmt_execute($stmt)) {
                        echo "Subject $subjectId attached successfully!"; // Debug message
                    } else {
                        $err[] = "Error attaching subject $subjectId: " . mysqli_error($con); // Add database error message
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $err[] = "Error preparing statement for subject $subjectId.";
                }
            }

            if (empty($err)) {
                // Success message
                $_SESSION['successMsg'] = 'Subjects attached to student successfully!';

                // Fetch updated list of attached subjects for this student with related information
                $strSqlAttachedSubjects = "SELECT s.subject_code, s.subject_name, ss.grade, ss.subject_id, s.id AS subject_id
                                           FROM subjects s
                                           LEFT JOIN students_subjects ss ON ss.subject_id = s.id
                                           WHERE ss.student_id = " . $_SESSION['k'] . "
                                           ORDER BY s.subject_code";

                $attachedSubjectsResult = mysqli_query($con, $strSqlAttachedSubjects);

                if ($attachedSubjectsResult && mysqli_num_rows($attachedSubjectsResult) > 0) {
                    $attachedSubjects = mysqli_fetch_all($attachedSubjectsResult, MYSQLI_ASSOC);
                } else {
                    $attachedSubjects = [];
                }
            }
        }
    } else {
        $err[] = 'No subjects selected!';
    }
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Attach Subjects To Student</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item">Attach Subjects To Student</li>
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

        <h1 class="h5 fw-normal">Select Subjects</h1>

        <div class="form-group mb-3">
            <label>Select subjects to attach:</label><br>
            <?php foreach ($subjects as $subject): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="subject_ids[]" value="<?php echo $subject['id']; ?>" id="subject_<?php echo $subject['id']; ?>">
                    <label class="form-check-label" for="subject_<?php echo $subject['id']; ?>">
                        <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="d-flex">
            <button class="btn btn-primary" type="submit" name="btnAttach" id="btnAttach">Attach Subjects</button>
        </div>
    </form>

    <h2 class="h5 fw-normal mt-4">Attached Subjects</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Grade</th>
                <th class="text-center">Option</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($attachedSubjects) && !empty($attachedSubjects)) {
                foreach ($attachedSubjects as $subject) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($subject['subject_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($subject['subject_name']) . '</td>';
                    echo '<td>' . (isset($subject['grade']) ? htmlspecialchars($subject['grade']) : 'No grade assigned') . '</td>';
                    echo '<td class="text-center">'; 

                    // If no grade is assigned, show "Assign Grade"
                    echo '<a href="assign-grade.php?subject_id=' . $subject['subject_id'] . '" class="btn btn-warning me-2">Assign Grade</a>';

                    // Detach button to remove subject from the student
                    echo '<a href="detach-subject.php?subject_id=' . $subject['subject_id'] . '" class="btn btn-danger">Detach</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="4" class="text-center">No subjects attached.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</main>

<?php require_once('../partials/footer.php'); ?>
