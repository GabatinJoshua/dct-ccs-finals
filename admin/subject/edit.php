<?php 
session_start();
require_once('../partials/header.php');
require_once('../partials/side-bar.php');
guard();

 ?>


 <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
 	<nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'subject' ? 'active' : ''); ?>">Add Subject</li>
            <li class="breadcrumb-item">Edit Subject</li>
        </ol>
    </nav>

 	<form>
 		 <div class="form-group mb-3"> 
            <input type="text" class="form-control" id="txtSubjectCode" name="txtSubjectCode" placeholder="Subject Code">
        </div>
        <div class="form-group mb-3">
            <input type="text" class="form-control" id="txtSubjectName" name="txtSubjectName" placeholder="Subject Name">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100" name="btnAdd" id="btnAdd">Update Subject</button>
        </div>
 	</form>
 </main>



 <?php require_once ('../partials/footer.php');?>