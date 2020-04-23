<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

/* if form was submitted, then execute query to search for adopter */

$query = "# Get the dates for the last 12 months
WITH recursive Date_Ranges AS (
   select DATE_ADD(DATE_ADD(LAST_DAY(now() ), INTERVAL 1 DAY), INTERVAL - 13 MONTH) as Date
   union all
   select Date + interval 1 day
   from Date_Ranges
   where Date <= LAST_DAY(now() - INTERVAL 1 MONTH) ),
   
YearMonth AS (SELECT DISTINCT EXTRACT(MONTH from Date) AS month, EXTRACT(YEAR from Date) AS year
              FROM Date_Ranges),

# Get the correct pet_ids
AdoptionData AS (SELECT adoption.pet_id, species, breed, adoption_date,
	   				  EXTRACT(MONTH from adoption_date) AS month,
	   				  EXTRACT(YEAR from adoption_date) AS year,
                      LAST_DAY(now() - INTERVAL 1 MONTH) AS last_day_previous_month,
                      DATE_ADD(DATE_ADD(LAST_DAY(now() ), INTERVAL 1 DAY), INTERVAL - 13 MONTH) AS first_day
FROM adoption
LEFT JOIN animals
ON adoption.pet_id = animals.pet_id
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          	          FROM breeds
                      GROUP BY pet_ID) AS breeds
ON adoption.pet_id = breeds.pet_id
), 

# Filter on the dates
CorrectAdoptionData AS (SELECT *
FROM AdoptionData
WHERE adoption_date >= first_day AND adoption_date <= last_day_previous_month
ORDER BY adoption_date),

# Get the subtotals
SpeciesAdoptionCount AS (SELECT COUNT(DISTINCT pet_id) AS monthly_adoptions, 
   year, month, species, 'subtotal' as breed
FROM CorrectAdoptionData 
GROUP BY year, month, species),

# Get the totals per species, breed, month, year
SpeciesBreedAdoptionCount AS (SELECT COUNT(DISTINCT pet_id) AS monthly_adoptions, year, month, species, breed
FROM CorrectAdoptionData
GROUP BY year, month, species, breed),

# Get the subtotal and the breeds counts                              
AdoptionsCount AS (
    	SELECT monthly_adoptions, year, month, species, breed
		FROM SpeciesAdoptionCount
		UNION 
		SELECT monthly_adoptions, year, month, species, breed
		FROM SpeciesBreedAdoptionCount),                           
# Now the same for surrenders                              


# Get the correct pet_ids
SurrenderData AS (SELECT animals.pet_id, species, breed, surrender_date,
	   				  EXTRACT(MONTH from surrender_date) AS month,
	   				  EXTRACT(YEAR from surrender_date) AS year,
                      LAST_DAY(now() - INTERVAL 1 MONTH) AS last_day_previous_month,
                      DATE_ADD(DATE_ADD(LAST_DAY(now() ), INTERVAL 1 DAY), INTERVAL - 13 MONTH) AS first_day
FROM animals
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          	          FROM breeds
                      GROUP BY pet_ID) AS breeds
ON animals.pet_id = breeds.pet_id
), 

# Filter on the dates
CorrectSurrenderData AS (SELECT *
FROM SurrenderData
WHERE surrender_date >= first_day AND surrender_date <= last_day_previous_month
ORDER BY surrender_date),

# Get the subtotals
SpeciesSurrenderCount AS (SELECT COUNT(DISTINCT pet_id) AS monthly_surrenders, 
   year, month, species, 'subtotal' as breed
FROM CorrectSurrenderData 
GROUP BY year, month, species),

# Get the totals per species, breed, month, year
SpeciesBreedSurrenderCount AS (SELECT COUNT(DISTINCT pet_id) AS monthly_surrenders, year, month, species, breed
FROM CorrectSurrenderData
GROUP BY year, month, species, breed),

# Get the subtotal and the breeds counts                              
SurrenderCount AS (
    	SELECT monthly_surrenders, year, month, species, breed
		FROM SpeciesSurrenderCount
		UNION 
		SELECT monthly_surrenders, year, month, species, breed
		FROM SpeciesBreedSurrenderCount),                               
# Get all month,year, species and breed combinations
# Note per the description: 'Only breeds and species adopted during the 12 month period should be displayed.'

SpeciesBreeds AS (SELECT *
                  FROM (SELECT DISTINCT species, breed FROM CorrectAdoptionData) as species_breed_types
                  CROSS JOIN YearMonth),
                  
Results AS (SELECT SpeciesBreeds.year, SpeciesBreeds.month, SpeciesBreeds.species, SpeciesBreeds.breed, 
	   IFNULL(monthly_surrenders, '') as monthly_surrenders, 
       IFNULL(monthly_adoptions, '') as monthly_adoptions
FROM SpeciesBreeds 
LEFT JOIN AdoptionsCount
ON SpeciesBreeds.species = AdoptionsCount.species
AND SpeciesBreeds.breed = AdoptionsCount.breed
AND SpeciesBreeds.year = AdoptionsCount.year
AND SpeciesBreeds.month = AdoptionsCount.month
LEFT JOIN SurrenderCount
ON SpeciesBreeds.species = SurrenderCount.species
AND SpeciesBreeds.breed = SurrenderCount.breed
AND SpeciesBreeds.year = SurrenderCount.year
AND SpeciesBreeds.month = SurrenderCount.month
ORDER BY year ASC, month ASC, species ASC, breed ASC)

SELECT CASE WHEN month >= 10 THEN CONCAT( CAST(year AS CHAR), \"-\", CAST(month AS CHAR) )
            ELSE CONCAT( CAST(year AS CHAR), \"-0\", CAST(month AS CHAR) )
	   END AS date,
       species, breed, monthly_surrenders, monthly_adoptions
FROM Results";


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
<title>Monthly Adoptions</title>
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
MONTHLY ADOPTION</h2></center>
<br>

<div class="container">
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle" style="color:white;">Monthly Adoptions & Surrenders Report</div>
                <div class='profile_section'>
                    <?php
                    if ((mysqli_num_rows($result) > 0)) {

                        print "<div class='subtitle' style='color:white;'>List of Monthly Adoptions and Surrenders</div>";
                        print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>Date</th><th>Species</th><th>Breed</th><th>Monthly Surrenders</th><th>Monthly Adoption</th> </tr>";

                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                           print "<tr><td>" . $row['date'] . "</td><td>" . $row['species'] . "</td><td>" . $row['breed'] . "</td><td>" . $row['monthly_surrenders'] . "</td><td>" . $row['monthly_adoptions'] . "</td> </tr>";
                          
                            
                        }
						print "</table>";
                            print "<br>";
						}
                    elseif (isset($result) && (mysqli_num_rows($result) == 0)) {
                        print "<div class='subtitle'>Sorry, no monthly adoptions or surrenders found found</div>";
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
