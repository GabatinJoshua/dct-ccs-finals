<?php
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
require_once('../partials/db-connection.php'); // Make sure to include the connection file

guard();

// Make sure the connection is established
$con = openConnection();

if (isset($_GET['subject_id'])) {
    $subjectId = $_GET['subject_id'];

    // Fetch the subject details to display in the form
    $strSqlSubject = "SELECT subject_code, subject_name FROM subjects WHERE id = ?";
    if ($stmt = mysqli_prepare($con, $strSqlSubject)) {
        mysqli_stmt_bind_param($stmt, "i", $subjectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $subject = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }

    // If subject details are found
    if ($subject) {
        echo '<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">';
        echo '<h1 class="h3 fw-normal">Assign Grade for ' . htmlspecialchars($subject['subject_code']) . ' - ' . htmlspecialchars($subject['subject_name']) . '</h1>';
        echo '<form method="POST" class="border p-4 rounded">';

        // Grade input field
        echo '<div class="mb-3">';
        echo '<label for="grade" class="form-label">Grade</label>';
        echo '<input type="text" class="form-control" id="grade" name="grade" placeholder="Enter grade" required>';
        echo '</div>';

        // Submit button
        echo '<button type="submit" class="btn btn-primary">Assign Grade</button>';
        echo '</form>';

        // Handle form submission to assign grade
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade'])) {
            $grade = $_POST['grade'];

            // Ensure the grade is a valid input (you can add further validation if needed)
            if (!empty($grade)) {
                // Update the grade for this subject
                $updateGradeSql = "UPDATE student_subjects SET grade = ? WHERE student_id = ? AND subject_id = ?";
                if ($stmt = mysqli_prepare($con, $updateGradeSql)) {
                    mysqli_stmt_bind_param($stmt, "sii", $grade, $_SESSION['k'], $subjectId);
                    mysqli_stmt_execute($stmt);

                    if (mysqli_stmt_affected_rows($stmt) > 0) {
                        echo '<div class="alert alert-success mt-3">Grade assigned successfully!</div>';
                    } else {
                        echo '<div class="alert alert-danger mt-3">Error assigning grade. Please try again or check if the grade has already been assigned.</div>';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    echo '<div class="alert alert-danger mt-3">Error preparing the SQL statement. Please try again.</div>';
                }
            } else {
                echo '<div class="alert alert-warning mt-3">Please enter a grade.</div>';
            }
        }
        
        echo '</main>';
    } else {
        echo '<div class="alert alert-warning mt-3">Subject not found!</div>';
    }
}
?>
