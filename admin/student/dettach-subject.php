<?php
// Assuming database connection is already open

if (isset($_GET['subject_id'])) {
    $subjectId = $_GET['subject_id'];
    
    // Fetch student and subject details to display
    $strSqlDetails = "SELECT s.subject_code, s.subject_name, st.student_id, st.first_name, st.last_name
                      FROM student_subjects ss
                      JOIN students st ON ss.student_id = st.id
                      JOIN subjects s ON ss.subject_id = s.id
                      WHERE ss.student_id = ? AND ss.subject_id = ?";
    
    if ($stmt = mysqli_prepare($con, $strSqlDetails)) {
        mysqli_stmt_bind_param($stmt, "ii", $_SESSION['k'], $subjectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $subjectDetails = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    // Check if data exists
    if ($subjectDetails) {
        echo '<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">';
        echo '<h1 class="h3 fw-normal">Detach Subject: ' . htmlspecialchars($subjectDetails['subject_code']) . ' - ' . htmlspecialchars($subjectDetails['subject_name']) . '</h1>';
        
        // Display the details of the student and subject
        echo '<h4 class="fw-normal">Student Information</h4>';
        echo '<ul>';
        echo '<li><strong>Student ID:</strong> ' . htmlspecialchars($subjectDetails['student_id']) . '</li>';
        echo '<li><strong>Student Name:</strong> ' . htmlspecialchars($subjectDetails['first_name']) . ' ' . htmlspecialchars($subjectDetails['last_name']) . '</li>';
        echo '<li><strong>Subject Code:</strong> ' . htmlspecialchars($subjectDetails['subject_code']) . '</li>';
        echo '<li><strong>Subject Name:</strong> ' . htmlspecialchars($subjectDetails['subject_name']) . '</li>';
        echo '</ul>';

        // Display the confirmation and detach form
        echo '<form method="POST" class="border p-4 rounded">';
        echo '<div class="alert alert-warning">Are you sure you want to detach this subject?</div>';
        
        // Detach Button
        echo '<button type="submit" class="btn btn-danger">Detach Subject</button>';
        echo '</form>';

        // Handle form submission to detach the subject
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $strSqlDetach = "DELETE FROM student_subjects WHERE student_id = ? AND subject_id = ?";
            if ($stmt = mysqli_prepare($con, $strSqlDetach)) {
                mysqli_stmt_bind_param($stmt, "ii", $_SESSION['k'], $subjectId);
                mysqli_stmt_execute($stmt);

                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    echo '<div class="alert alert-success mt-3">Subject detached successfully!</div>';
                } else {
                    echo '<div class="alert alert-danger mt-3">Error detaching the subject. Please try again.</div>';
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        echo '</main>';
    } else {
        echo '<div class="alert alert-warning mt-3">Subject not found or not attached to this student!</div>';
    }
}
?>
