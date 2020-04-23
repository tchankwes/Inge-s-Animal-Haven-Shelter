<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
  }

$app_id = htmlspecialchars($_GET['app_id']);

if (isset($app_id)) {

    $query = "UPDATE adoptionapplication
              SET applicant_status = 'approved'
              WHERE applicant_number = $app_id
              AND applicant_status = 'pending approval'";
    $result = mysqli_query($db, $query);

    include ('lib/show_queries.php');
    $inserted = FALSE;
    if (mysqli_affected_rows($db) == -1) {
        array_push($error_msg, "adoption insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );

    }
    else {
        $inserted = TRUE;
    }

}

/* if form was submitted, then execute query to search for adopter */

$query = "SELECT applicant_first_name, applicant_last_name, 
	            co_applicant_first_name, co_applicant_last_name, 
               street, city, state, zip, phone_number, adopter.email_address, adoptionapplication.applicant_number
FROM adoptionapplication
INNER JOIN adopter
ON adoptionapplication.email_address = adopter.email_address
LEFT JOIN coadopter
ON adoptionapplication.email_address = coadopter.email_address
AND adoptionapplication.applicant_number = coadopter.applicant_number
WHERE applicant_status = 'pending approval'
ORDER BY adopter.email_address";
$result = mysqli_query($db, $query);
include('lib/show_queries.php');
if (mysqli_num_rows($result) == 0) {
            array_push($error_msg, "Failed to find adoption applications to review ... <br>" . __FILE__ ." line:". __LINE__ );
        }




?>
<head>
<title>Customer Lookup</title>
<link rel="stylesheet" href="css/bootstrap.css">
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="css/style.css">
  <!-- IE-only CSS -->
 </head>
 <body style=" background-image:url(img/2.jpg); background-attachment:fixed;">

 <?php include("lib/menu.php"); ?>
 <br><br><br>
 <center><h2 style="font-weight:bold; color:#FFFFFF; text-shadow: 2px 2px 5px red;">
ADOPTION APPLICATION REVIEW</h2></center>
<br>
<center>
<div class="containegr" style="width:95%; float:center;">
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle">Adoption Application Review</div>
                </div>

                <?php
                if (isset($app_id) && $inserted ) {
                    print "<div class='subtitle' style='color:white;'>Adoption application #$app_id approved</div>";

                }

                ?>

                <div class='profile_section'>
              <?php
              if (isset($result) && (mysqli_num_rows($result) > 0)) {
			  $caf="NAN";
			  $cal="NAN";

                  print "<div class='subtitle' style='color:white;'>List of Adoption Applications</div>";
                  print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>First Name</th><th>Last Name</th><th>Co-applicant First Name</th><th>Co-applicant Last Name</th><th>Street</th><th>City</th><th>State</th><th>ZIP</th><th>Phone #</th><th>Email address</th><th>Aprove</th></tr>";

                  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                  {
				  
				   if (isset($row['co_applicant_first_name'])) {
                          $caf=$row['co_applicant_first_name'];
                         $cal=$row['co_applicant_last_name'];
                      }
				  
				  print "<tr><td>" . $row['applicant_first_name'] . "</td><td>" . $row['applicant_last_name'] . "</td><td>" . $caf . "</td><td>" . $cal . "</td><td>" . $row['street'] . "</td><td>" . $row['city'] . "</td><td>" . $row['state'] . "</td><td>" . $row['zip'] . "</td><td>" . $row['phone_number'] . "</td><td>" . $row['email_address'] . "</td><td><a class='btn btn-primary' href='adoption_application_review.php?app_id=" .  urlencode($row['applicant_number'])
                          .  "'>Approve this adopter</a></td></tr>";
                     
                     

                  }}
                  elseif (isset($result) && (mysqli_num_rows($result) == 0)) {
                  print "<div class='subtitle'>Sorry, no adoption application found</div>";
              }


                  print "</table>";



              ?>


</center>
						</div>
          </div>
				</div>
        <?php include("lib/error.php"); ?>
        <div class="clear"></div>
			</div>
      <?php include("lib/footer.php"); ?>
		</div>
	</body>
</html>
