<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

/* Get the year and months and counts*/

$month_query = "SELECT DISTINCT EXTRACT(MONTH from date_worked) AS month
FROM VolunteerHours
ORDER BY month";

$year_query = "SELECT DISTINCT 
	   	           EXTRACT(YEAR from date_worked) AS year
FROM VolunteerHours
ORDER BY year";


$year_results = mysqli_query($db, $year_query);


$month_results = mysqli_query($db, $month_query);

/* if form was submitted, then execute query to search for adopter */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $query_ready = TRUE;

    if (empty($_POST["year"]) && empty($_POST["month"]))  {
        $query_ready = FALSE;
    } else {

        $year = $_POST["year"];
        $month = $_POST["month"];
    }

    if ($query_ready) {

        $volunteers_query ="with VolunteerData AS (SELECT VolunteerHours.username, date_worked, hours,
	   EXTRACT(MONTH from date_worked) AS month,
	   EXTRACT(YEAR from date_worked) AS year,
	   first_name,
	   last_name,
	   email
FROM VolunteerHours
LEFT JOIN User
ON VolunteerHours.username = User.username),

VolunteerGrouping AS (SELECT year, month, SUM(hours) AS monthly_hours, first_name, last_name, email
FROM VolunteerData
GROUP BY month, year, first_name, last_name, email),

VolunteerRanks AS (SELECT year, month, monthly_hours, first_name, last_name, email,
	   RANK() OVER ( PARTITION BY year, month ORDER BY monthly_hours DESC, 
      last_name ASC) 
               AS monthly_ranks
FROM VolunteerGrouping)

SELECT year, month, monthly_hours, first_name, last_name, email
FROM VolunteerRanks
WHERE monthly_ranks <= 5
AND year = $year
AND month = $month
ORDER BY year, month, monthly_ranks";



        }
    }

        $volunteers_result = mysqli_query($db, $volunteers_query);
        include('lib/show_queries.php');
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "Failed to find Volunteers ... <br>" . __FILE__ ." line:". __LINE__ );
        }




 ?>
<head>
<title>Volunteer of the Month</title>
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
VOLUNTEER OF THE MONTH</h2></center>
<br>

<div class="container">
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle" style="color:white;">Monthly Details Selection</div>
                    <form name = 'monthly_selection' action="volunteer_of_the_month.php" method="post">
                        <?php
                        echo "<select name='month' style='width:60px; height:30px;'>";
                            while ($row = mysqli_fetch_array($month_results)) {
                            echo "<option value='" . $row['month'] ."'>" . $row['month'] ."</option>";
                            }
                            echo "</select>";
                        echo "<select name='year' style='width:60px; height:30px;'>";
                            while ($row = mysqli_fetch_array($year_results)) {
                                echo "<option value='" . $row['year'] ."'>" . $row['year'] ."</option>";
                            }
                            echo "</select>";
                        ?>
                        <input type="submit" value="Submit" class="btn btn-primary">
                    </form>
                </div>


                <div class='profile_section'>
                    <?php

                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($volunteers_result) && (mysqli_num_rows($volunteers_result) > 0)) {

                        print "<div class='subtitle' style='color:white;'>Top volunteers for $year-$month</div>";
                         print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Monthly Hours</th><th>First Name</th><th>Last Name</th><th>Email</th></tr>";

                        while ($row = mysqli_fetch_array($volunteers_result, MYSQLI_ASSOC))
                        {
                            print "<tr><td>" . $row['monthly_hours'] . "</td><td>" . $row['first_name'] . "</td><td>" . $row['last_name'] . "</td><td>" . $row['email'] . "</td></tr>";
                       
                            
                        }
						print "</table>";
                            print "<br>";
						}
                    elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($volunteers_result) && (mysqli_num_rows($volunteers_result) == 0)) {
                        print "<div class='subtitle'>Sorry, no volunteers or month is out of bounds</div>";
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
