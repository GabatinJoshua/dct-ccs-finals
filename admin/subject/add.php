<?php 
    session_start();
    $_SESSION['CURR_PAGE'] = 'subject';
    require_once('../partials/header.php');
    require_once('../partials/side-bar.php');
    guard();


 ?>



<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-5">
	<nav aria-label="breadcrumb">
	  <ol class="breadcrumb">
	    <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'dashboard' ? 'active' : ''); ?>"><a href="../dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item <?php echo ($_SESSION['CURR_PAGE'] == 'subject' ? 'active' : ''); ?>">Add Subject</li>
	  </ol>
	</nav>

    <form class="border p-4 rounded">
        <div class="form-group mb-3"> <!-- Added margin bottom for space -->
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Subject Code">
        </div>
        <div class="form-group mb-3"> <!-- Added margin bottom for space -->
            <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Subject Name">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100">Add Subject</button> <!-- w-100 makes the button full width -->
        </div>
    </form><br><br><br>

    <form class="border p-4 rounded">
    	<table class="table">
		  <thead>
		    <tr>
		      <th>Subject Code</th>
		      <th>Subjct Name</th>
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
						    echo '<td>' . $value['subject_code'] .'</td>'; 
						    echo '<td>' . $value['subject_name'] .'</td>';
						    echo '<td class="text-center">';
						    	echo '<a href="edit.php?k=' . $value['id'] . '" class ="btn btn-success">Edit</a>';
						    	echo '<a href="delete.php?k=' . $value['id'] . '" class ="btn btn-danger">Delete</a>';
						    echo '<td>';
						echo '</tr>';
		    		}
		    	}else{

		    	}
     		?>
		    
		    
		  </tbody>
		</table>
    </form>

 
</main>



<?php require_once ('../partials/footer.php');?>