<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

/* if form was submitted, then execute query to search for adopter */

$query = "# Get the dates for the next 3 months
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE() + INTERVAL 90 DAY ),

# Get the year and months to pull
YearMonth AS (SELECT DISTINCT
   			    EXTRACT(MONTH from Date) as month,
	    		EXTRACT(YEAR from Date) as year
FROM Date_Ranges),

VaccinationData AS (SELECT pet_id, vaccination_type, expiration_date, username,
                            EXTRACT(MONTH from expiration_date) as month,
                            EXTRACT(YEAR from expiration_date) as year
FROM VaccinationAdministered),

SoonVaccinations AS (SELECT pet_id, vaccination_type, expiration_date, username
                     FROM VaccinationData
                     INNER JOIN YearMonth
                     ON VaccinationData.month = YearMonth.month
                     AND VaccinationData.year = YearMonth.year),

VaccinationsAnimal AS (SELECT animals.pet_id, species, breed, sex, alteration_status, microchip_id
FROM animals
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          	          FROM breeds
                      GROUP BY pet_ID) AS breeds
ON animals.pet_id = breeds.pet_id
WHERE animals.pet_id IN (SELECT DISTINCT pet_id FROM SoonVaccinations) ),


UserResponsibleForInput AS (SELECT User.username, first_name, last_name
FROM User
INNER JOIN (SELECT DISTINCT username FROM SoonVaccinations ) as Soon_Vaccination_u
ON User.username = Soon_Vaccination_u.username )
                            
SELECT SoonVaccinations.pet_id, vaccination_type, expiration_date, species, breed, sex, alteration_status, 
   microchip_id, first_name, last_name
FROM SoonVaccinations
INNER JOIN VaccinationsAnimal
ON SoonVaccinations.pet_id = VaccinationsAnimal.pet_id
INNER JOIN UserResponsibleForInput
ON SoonVaccinations.username = UserResponsibleForInput.username
ORDER BY expiration_date, SoonVaccinations.pet_id";


        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "Failed to find Recent Vaccinations ... <br>" . __FILE__ ." line:". __LINE__ );
        }


//    if ( !is_bool($result) && (mysqli_num_rows($result) > 0) ) {
//        $row =  mysqli_fetch_array($result, MYSQLI_ASSOC);
//        $showup = TRUE;
//    } else {
//        array_push($error_msg,  "Query ERROR: No adopter found...<br>" . __FILE__ ." line:". __LINE__ );
//    }


 ?>
<head>
<title>Vaccine Reminders Report</title>
<body>
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
VACCINATION REMINDER</h2></center>
<br>

<div class="container">
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle" style="color:white;">Vaccine Reminders Report</div>
                <div class='profile_section'>
                    <?php
                    if ((mysqli_num_rows($result) > 0)) {

                        print "<div class='subtitle' style='color:white;'><h3 style='color:white;'>List of Vaccines</h3></div>";
                        print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Pet ID</th><th>Vaccination Type</th><th>Expiration Date</th><th>Species</th><th>Breed</th><th>Sex</th><th>Alteration Status</th><th>Microchip ID</th><th>First Name</th><th>Last Name</th> </tr>";

                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                            print "<tr><td>" . $row['pet_id'] . "</td><td>" . $row['vaccination_type'] . "</td><td>" . $row['expiration_date'] . "</td><td>" . $row['species'] . "</td><td>" . $row['breed'] . "</td><td>" . $row['sex'] . "</td><td>" . $row['alteration_status'] . "</td><td>" . $row['microchip_id'] . "</td><td>" . $row['first_name'] . "</td><td>" . $row['last_name'] . "</td> </tr>";
                           
                            
                        }
						print "</table>";
                            print "<br>";
						}
                    elseif (isset($result) && (mysqli_num_rows($result) == 0)) {
                        print "<div class='subtitle'>Sorry, no vaccines found</div>";
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
