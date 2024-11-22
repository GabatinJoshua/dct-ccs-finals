<?php session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();
 ?>

 <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
    <h1 class="h3 fw-normal">Delete Subject</h1><br>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="register.php">Register Student</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'student' ? 'active' : ''); ?>"><a href="attach-subject.php">Attach Subjects To Student</a></li>        
            <li class="breadcrumb-item">Dettach Subject to Student</li>
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

        <p class="mb-3">Are you sure you want to delete the following record?</p>

        <ul class="mb-3">
            <li>
                <strong>Student ID:</strong> <?php echo htmlspecialchars($recPersons['student_id']); ?>
            </li>
            <li>
                <strong>First Name:</strong> <?php echo htmlspecialchars($recPersons['first_name']); ?>
            </li>
            <li>
                <strong>Last Name:</strong> <?php echo htmlspecialchars($recPersons['last_name']); ?>
            </li>
        </ul>

        <div class="d-flex">
            <button class="btn btn-secondary me-2" type="button" id="btnCancel" onclick="window.location.href='register.php'">Cancel</button>
            <button class="btn btn-danger" type="submit" name="btnDelete" id="btnDelete">Delete Subject Record</button>
        </div>
    </form>

</main>

 <?php require_once('../partials/footer.php'); ?>