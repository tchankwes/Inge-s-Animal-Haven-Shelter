<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
  }

$pet_id = htmlspecialchars($_GET['pet_id']);

$name_input_err = "";

/* if form was submitted, then execute query to search for adopter */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $query_ready = TRUE;

    if (empty($_POST["name_input"]))  {
        $query_ready = FALSE;
        $name_input_err = "please input last name";
    }
    else {
        $name_input = ($_POST['name_input']);
    }

    if ($query_ready) {
        $query = "SELECT applicant_first_name, applicant_last_name, 
                    co_applicant_first_name, co_applicant_last_name,
                    street, city, state, zip, phone_number, adopter.email_address, adoptionapplication.applicant_number
                    FROM adopter
                    INNER JOIN adoptionapplication
                    ON adopter.email_address = adoptionapplication.email_address
                    LEFT JOIN coadopter
                    ON adopter.email_address = coadopter.email_address
                    AND adoptionapplication.applicant_number = coadopter.applicant_number
                    WHERE applicant_status = 'approved' 
                    AND adoptionapplication.applicant_number NOT IN (SELECT applicant_number 
                        FROM adoption)
                    AND (applicant_last_name LIKE '%$name_input%'
                     OR co_applicant_last_name LIKE '%$name_input%')
                    ORDER BY adopter.email_address";
        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "Failed to find adopter ... <br>" . __FILE__ ." line:". __LINE__ );
        }
    }

  }
?>

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
 <div class="main"><br><br><br>
 <center><h2 style="font-weight:bold; color:#FFFFFF; text-shadow: 2px 2px 5px red;">
CUSTOMER LOOKUP</h2></center>
<br>							   
<div class="container">

      <div class="center_content">
				<div class="center_left">          
				  <div class="adopter">
            <div class="profile_section">						
							<div class="subtitle">Adopter Lookup</div>
							<form name="search_form" action="adopter_lookup.php?pet_id=<?php echo $pet_id; ?>" method="POST">
								<table>
                 	<tr>
                    <td class="addanitab item_label">Enter last name</td>
						<td><input type="text" name="name_input" value="<?php echo $name_input; ?>" />
                    </td>    <span class="error_msg">* <?php echo $name_input_err;?></span>

									</tr>

								</table>
									<a href="javascript:search_form.submit();" class="btn btn-primary fancy_button">Search</a>
							</form>							
						</div>
						
						<div class='profile_section'>
              <?php
              if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($result) && (mysqli_num_rows($result) > 0)) {
$caf="NAN";
			  $cal="NAN";
                  print "<div class='subtitle'>Approved adopter</div>";
                   print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";

                  print "<tr><th>
                            First Name</th>
                            <th>Last Name</th>
                            <th>Co_app First Name</th>
                            <th>Co_app Last Name</th>
                            <th>Street</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Zip Code</th>
                            <th>Phone #</th>
                            <th>Email</th>
                            <th>Aprove</th></tr>"
                  ;

                  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                  {
					  if (isset($row['co_applicant_first_name'])) {
                          $caf=$row['co_applicant_first_name'];
                         $cal=$row['co_applicant_last_name'];
                      }
				  
				  print "<tr><td>" . $row['applicant_first_name'] . "</td><td>" . $row['applicant_last_name'] . "</td><td>" . $caf . "</td><td>" . $cal . "</td><td>" . $row['street'] . "</td><td>" . $row['city'] . "</td><td>" . $row['state'] . "</td><td>" . $row['zip'] . "</td><td>" . $row['phone_number'] . "</td><td>" . $row['email_address'] . "</td><td><a class='btn btn-primary' href='add_adoption.php?app_id=" .  urlencode($row['applicant_number'])

/* 
                      $first_name = $row['applicant_first_name'];
                      $last_name = $row['applicant_last_name'];
                      $co_first_name = $row['co_applicant_first_name'];
                      $co_last_name = $row['co_applicant_last_name'];
                      $street = $row['street'];
                      $city = $row['city'];
                      $state = $row['state'];
                      $zip = $row['zip'];
                      $phone = $row['phone_number'];
                      $email = $row['email_address'];
                      $app_id = $row['applicant_number'];

                      print "
                      <tr><td>$first_name</td>
                      <td>$last_name</td>
                      <td>$co_first_name</td>
                      <td>$co_last_name</td>
                      <td>$street</td>
                      <td>$city</td>
                      <td>$state</td>
                      <td>$zip</td>
                      <td>$phone</td>
                      <td>$email</td>
                      <td><a href='add_adoption.php?app_id=" .  urlencode($app_id) */
                          . "&pet_id=" . urldecode($pet_id).  "'>Select this adopter</a> </td> </tr>

                      ";

                  }}
                  elseif (isset($result) && (mysqli_num_rows($result) == 0)) {
                  print "<div class='subtitle'>Sorry, no adopter found</div>";
              }
                  print "</table>";

              ?>
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
