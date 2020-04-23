<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

/* Get the year and months and counts*/

$month_query = "# Get the dates for the last 6 months
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() - INTERVAL 180 DAY as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE())

# Get the year and months to pull
SELECT DISTINCT EXTRACT(MONTH from Date) as month
FROM Date_Ranges";

$year_query = "# Get the dates for the last 6 months
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() - INTERVAL 180 DAY as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE())

# Get the year and months to pull
SELECT DISTINCT 
	    		EXTRACT(YEAR from Date) as year
FROM Date_Ranges";

$initial_query = "# Get the dates for the last 6 months
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() - INTERVAL 180 DAY as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE()),

# Get the year and months to pull
YearMonth AS (SELECT DISTINCT
   			    EXTRACT(MONTH from Date) as month,
	    		EXTRACT(YEAR from Date) as year
FROM Date_Ranges),

# Get animals surrendered by animal control including the year and month of surrender
brought_by_animal_control AS (SELECT pet_id, surrender_date, 
	    	 EXTRACT(MONTH from surrender_date) as month,
	    	 EXTRACT(YEAR from surrender_date) as year 
      FROM animals
	  WHERE brought_by_animal_control = 1),
 
# Get the ones that happen in the last 6 months
brought_by_animal_control_current_months AS (SELECT pet_id, surrender_date, YearMonth.month, YearMonth.year
FROM brought_by_animal_control
RIGHT JOIN YearMonth
ON brought_by_animal_control.month = YearMonth.month
AND brought_by_animal_control.year = YearMonth.year),

# Get the monthly counts of pets surrendered by animal control with 0 if there aren’t any
brought_by_animal_control_count AS (SELECT IFNULL(COUNT(DISTINCT pet_id), 0) as pets_brought_by_animal_control, year, month
FROM brought_by_animal_control_current_months
GROUP BY year, month
ORDER BY year, month ),


# Get the animal data
AnimalData AS (SELECT animals.pet_id, surrender_date, adoption_date
FROM animals
INNER JOIN adoption
ON animals.pet_id = adoption.pet_id),

# Get the animals that have been for 60 days in the shelter
AdoptedPetsPerYearMonthLongCount AS (SELECT pet_id,
    	EXTRACT(MONTH from adoption_date) as month,
	EXTRACT(YEAR from adoption_date) as year
FROM AnimalData
WHERE DATEDIFF(adoption_date, surrender_date) >= 60),

AdoptedPetsCount AS (SELECT count(DISTINCT pet_id) as long_stay_adoptions, month, year
FROM AdoptedPetsPerYearMonthLongCount
GROUP BY year, month)


# Join the count

SELECT brought_by_animal_control_count.month, brought_by_animal_control_count.year, 
	   IFNULL(long_stay_adoptions, 0) as long_stay_adoptions,
       pets_brought_by_animal_control
FROM brought_by_animal_control_count
LEFT JOIN AdoptedPetsCount
ON brought_by_animal_control_count.month = AdoptedPetsCount.month
AND brought_by_animal_control_count.year = AdoptedPetsCount.year";

$initial_result = mysqli_query($db, $initial_query);


$month_results = mysqli_query($db, $month_query);


$year_results = mysqli_query($db, $year_query);






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


        $surrender_query = "# Get animals surrendered by animal control including the year and month of surrender
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() - INTERVAL 180 DAY as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE()),

# Get the year and months to pull
YearMonth AS (SELECT DISTINCT
   			    EXTRACT(MONTH from Date) as month,
	    		EXTRACT(YEAR from Date) as year
FROM Date_Ranges),

brought_by_animal_control AS (SELECT pet_id, 
             name, sex, alteration_status, age, microchip_id, species,
             surrender_date,                       
	    	 EXTRACT(MONTH from surrender_date) as month,
	    	 EXTRACT(YEAR from surrender_date) as year 
      FROM animals
	  WHERE brought_by_animal_control = 1)

SELECT brought_by_animal_control.pet_id, species, breed, sex, alteration_status, microchip_id, surrender_date
FROM brought_by_animal_control
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          	          FROM breeds
                      GROUP BY pet_ID) AS breeds
ON brought_by_animal_control.pet_id = breeds.pet_id
INNER JOIN YearMonth
ON brought_by_animal_control.month = YearMonth.month
AND brought_by_animal_control.year = YearMonth.year
WHERE brought_by_animal_control.month = $month
AND brought_by_animal_control.year = $year
ORDER BY brought_by_animal_control.pet_id;
";
        $adoptions_query = "# Get animals surrendered by animal control including the year and month of surrender
WITH recursive Date_Ranges AS (
   select CURRENT_DATE() - INTERVAL 180 DAY as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= CURRENT_DATE()),

# Get the year and months to pull
YearMonth AS (SELECT DISTINCT
   			    EXTRACT(MONTH from Date) as month,
	    		EXTRACT(YEAR from Date) as year
FROM Date_Ranges),

# Get the animal data
AnimalData AS (SELECT animals.pet_id, surrender_date, adoption_date
FROM animals
INNER JOIN adoption
ON animals.pet_id = adoption.pet_id),

# Get the animals that have been for 60 days in the shelter
AdoptedPetsPerYearMonthLongCount AS (SELECT pet_id,
    EXTRACT(MONTH from adoption_date) as month,
	EXTRACT(YEAR from adoption_date) as year,
    DATEDIFF(adoption_date, surrender_date) as date_diff_adoption                                 
FROM AnimalData
WHERE DATEDIFF(adoption_date, surrender_date) >= 60)

SELECT AdoptedPetsPerYearMonthLongCount.pet_id, species, breed, sex, alteration_status, microchip_id, surrender_date, date_diff_adoption
FROM AdoptedPetsPerYearMonthLongCount
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          	          FROM breeds
                      GROUP BY pet_ID) AS breeds
ON AdoptedPetsPerYearMonthLongCount.pet_id = breeds.pet_id
LEFT JOIN animals
ON AdoptedPetsPerYearMonthLongCount.pet_id = animals.pet_id
INNER JOIN YearMonth
ON AdoptedPetsPerYearMonthLongCount.month = YearMonth.month
AND AdoptedPetsPerYearMonthLongCount.year = YearMonth.year
WHERE AdoptedPetsPerYearMonthLongCount.month = $month
AND AdoptedPetsPerYearMonthLongCount.year = $year
ORDER BY date_diff_adoption DESC;
";


        }
    }

        $surrender_result = mysqli_query($db, $surrender_query);
        include('lib/show_queries.php');
        if (mysqli_num_rows($surrender_result) == 0) {
            array_push($error_msg, "Failed to find Animals ... <br>" . __FILE__ ." line:". __LINE__ );
        }

        $adoptions_result = mysqli_query($db, $adoptions_query);
        include('lib/show_queries.php');
        if (mysqli_num_rows($adoptions_result) == 0) {
            array_push($error_msg, "Failed to find Animals ... <br>" . __FILE__ ." line:". __LINE__ );
        }


//include("lib/header.php"); ?>
<head>
<title>Volunteer Lookup</title>
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
ANIMAL CONTROL</h2></center>
<br>
<div class="container">
  
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle" style="color:white;">Monthly Details Selection</div>
                    <form name = 'monthly_selection' action="animal_control_report.php" method="post">
                        <?php
                        echo "<select name='month' >";
                            while ($row = mysqli_fetch_array($month_results)) {
                            echo "<option value='" . $row['month'] ."'>" . $row['month'] ."</option>";
                            }
                            echo "</select>";
                        echo "<select name='year'>";
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
                    if (isset($initial_result) && (mysqli_num_rows($initial_result) > 0)) {

                        print "<div class='subtitle' style='color:white;'>Monthly Adoptions and Surrenders</div>";
                        print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Year</th><th>Month</th><th>Long Stay Adoptions</th><th>Animal Control Surrenders</th> </tr>";

                        while ($row = mysqli_fetch_array($initial_result, MYSQLI_ASSOC))
                        {
                         //   print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
							
							print "<tr><td>". $row['year'] ."</td><td>" . $row['month'] . "</td><td>" . $row['long_stay_adoptions'] . "</td><td>" . $row['pets_brought_by_animal_control'] . "</td> </tr>";
                           
                           
                        }
						 print "</table>";
                            print "<br>";
						}
						
                    elseif (isset($initial_result) && (mysqli_num_rows($initial_result) == 0)) {
                        print "<div class='subtitle'>Sorry, no adoptions and surrenders found</div>";
                    }


                    print "</table>";



                    ?>
                </div>
                <div class='profile_section'>
                    <?php

                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($surrender_result) && (mysqli_num_rows($surrender_result) > 0)) {

                        print "<div class='subtitle'>Pets surrendered by animal control for $year-$month</div>";
                            print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Pet ID</th><th>Species</th><th>Breed</th><th>Sex</th><th>Alteration Status</th><th>Microchip ID</th><th>Surrender Date</th> </tr>";

                        while ($row = mysqli_fetch_array($surrender_result, MYSQLI_ASSOC))
                        {
						print "<tr><td>" . $row['pet_id'] . "</td><td>" . $row['species'] . "</td><td>" . $row['breed'] . "</td><td>" . $row['sex'] . "</td><td>" . $row['alteration_status'] . "</td><td>" . $row['microchip_id'] . "</td><td>" . $row['surrender_date'] . "</td> </tr>";
                           
                    
                            
                        }
						print "</table>";
                            print "<br>";
						}
                    elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($surrender_result) && (mysqli_num_rows($surrender_result) == 0)) {
                        print "<div class='subtitle'>Sorry, no pets surrendered by animal control or month is out of bounds</div>";
                    }

                    print "</table>";



                    ?>
                </div>
                <div class='profile_section'>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($adoptions_result) && (mysqli_num_rows($adoptions_result) > 0)) {

                        print "<div class='subtitle'>Pets that stayed 60 or more days in the shelter for $year-$month</div>";
                         print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Pet ID</th><th>Species</th><th>Breed</th><th>Sex</th><th>Alteration Status</th><th>Microchip ID</th><th>Surrender Date</th><th>Days to Adoption</th> </tr>";

                        while ($row = mysqli_fetch_array($adoptions_result, MYSQLI_ASSOC))
                        {
                           print "<tr><td>" . $row['pet_id'] . "</td><td>" . $row['species'] . "</td><td>" . $row['breed'] . "</td><td>" . $row['sex'] . "</td><td>" . $row['alteration_status'] . "</td><td>" . $row['microchip_id'] . "</td><td>" . $row['surrender_date'] . "</td><td>" . $row['date_diff_adoption'] . "</td> </tr>";
                        
                           
                        }
						
						 print "</table>";
                            print "<br>";
						}
                    elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($adoptions_result) && (mysqli_num_rows($adoptions_result) == 0)) {
                        print "<div class='subtitle'>Sorry, no pets that stayed 60 days or more in the shelter or month is out of bounds</div>";
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
