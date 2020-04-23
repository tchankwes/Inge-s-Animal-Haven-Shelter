<head>

   
	<style>
	div.relative {
  position: relative;
  border: 3px solid #73AD21;
} 

div.absolute {
  position: absolute;
  width:100%;
  top: 280px;
  
}
	</style>
</head>
			<div id="header">

			
			<div class="nav_bar">
				    
         
          <!-- Only show Reports drop-down menu option to Owner -->
<!--          -->
            
			<div class="relative">
<img src="img/menbetter.jpg" width="100%" height="350px" style="-webkit-box-shadow: -1px 10px 5px 0px rgba(232,223,245,1);
-moz-box-shadow: -1px 10px 5px 0px rgba(232,223,245,1);
box-shadow: -1px 10px 5px 0px rgba(232,223,245,1);">
</div>
			
             <div class="absolute">
       <div class="col-lg-12">
	    <div class="col-lg-2">
	<h3 style="text-shadow: 2px 2px 5px red; color:yellow;">	Hello: <?php echo $_SESSION["first_name"]; echo " "; echo $_SESSION["last_name"];?> </h3>
		</div>
                <div class="col-lg-8"> <br>
				<?php if(isset($_SESSION['employee_type']) && in_array($_SESSION['employee_type'], array('manager'))) { ?>
                  <a href="animal_control_report.php" class="btn btn-primary">Animal Control Report</a>
                  <a href="volunteer_of_the_month.php" class="btn btn-primary">Volunteer of the Month</a>
                  <a href="monthly_adoptions.php" class="btn btn-primary">Monthly Adoption Report</a>
                  <a href="volunteer_lookup.php" class="btn btn-primary">Volunteer Lookup</a>
                  <a href="vaccine_reminder.php" class="btn btn-primary">Vaccine Reminder Report</a>
				  <?php } ?>
                  </div>
            <div class="col-lg-2"> <br>
			<a href="animal_dashboard.php" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span >&nbsp; Home</a>
		<a href="logout.php" class="btn btn-danger"><span class="glyphicon glyphicon-log-out"></span>&nbsp; Logout</a>
		</div>
		   </div>
            </div>
         
          <!-- If not currently logged in, show Log In option -->
        <!--  <?php /*if(!isset($_SESSION['employee_type'])) { */?>
            <li><a href="login.php">Log In</a></li>-->
          <!-- Otherwise, if user is logged in, show Log Out option -->
          <?php /*} else { */?><!--
            <li><a href="logout.php">Log Out</a></li>    
           <?php /*} */?>   -->
				
			</div>