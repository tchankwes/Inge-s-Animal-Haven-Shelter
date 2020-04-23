<?php
//session_start();
//if (!isset($_SESSION["fname"])) {
//    header('Location: login.php');
//}

include('lib/common.php');

if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee', 'volunteer')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

$order = htmlspecialchars($_GET['order']);
$by = htmlspecialchars($_GET['by']);
$spe = htmlspecialchars($_GET['species']);
$adopt = htmlspecialchars($_GET['adopt']);

$query = "SELECT * FROM animaldashboard";

if (!empty($spe) && $spe != 'all' && !empty($adopt) && $adopt != 'all') {
    $query = $query . " WHERE species='$spe' and adoptability_status='$adopt'";
}
elseif (!empty($spe) && $spe != 'all' ) {
    $query = $query . " WHERE species='$spe' ";
}
elseif (!empty($adopt) && $adopt != 'all') {
    $query = $query . " WHERE adoptability_status='$adopt'";
}

if (!empty($order) && !empty($by)) {
    $query = $query . " ORDER BY $by $order";
}

$result = mysqli_query($db, $query);
include ('lib/show_queries.php');
if (mysqli_num_rows($result) == 0) {
    array_push($error_msg,  "SELECT ERROR:Failed to find animals ... <br>" . __FILE__ ." line:". __LINE__ );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {





    $spe = mysqli_real_escape_string($db, $_POST['species_selection']);
    $adopt = mysqli_real_escape_string($db, $_POST['adoptability_selection']);
    $order = htmlspecialchars($_GET['order']);
    $by = htmlspecialchars($_GET['by']);

    $query = "SELECT * FROM animaldashboard";

    if ($spe != 'all' && $adopt != 'all') {
        $query = $query . " WHERE species='$spe' and adoptability_status='$adopt'";
    }
    elseif ($spe != 'all' ) {
        $query = $query . " WHERE species='$spe' ";
    }
    elseif ($adopt != 'all') {
        $query = $query . " WHERE adoptability_status='$adopt'";
    }

    if (!empty($order) && !empty($by)) {
        $query = $query . " ORDER BY $by $order";
    }

    $result = mysqli_query($db, $query);
    include ('lib/show_queries.php');
    if (mysqli_num_rows($result) == 0) {
        array_push($error_msg,  "SELECT ERROR:Failed to find animals ... <br>" . __FILE__ ." line:". __LINE__ );
    }


}

?>

<html>
<head>

    <title>Dashboard</title>
    <link rel="stylesheet" href="css/bootstrap.css">
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="css/style.css">
  <!-- IE-only CSS -->
  
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
<body style=" background-image:url(img/2.jpg); background-attachment:fixed;">

 <?php include("lib/menu.php"); ?>

 <br>
 <div class="main">
 <div class="container" style="background-color:rgba(255, 255, 255, 0.9);"> <br>
 <center><h2 style="font-weight:bold; color:black; text-shadow: 2px 2px 5px red;">
ANIMAL DASHBOARD</h2></center>
<br>
    
<form name = 'species_selection_form' action="animal_dashboard.php" method="post">
    <tr>
        <tb>
            <select name = 'species_selection' onchange="this.form.submit();">
<!--                <option value="all"  --><?php //echo (isset($_POST['species_selection']) && $_POST['species_selection'] == 'all')?'selected="selected"':''; ?><!-->all</option>-->
                <option value="all"  <?php if (isset($_POST['species_selection']) && $_POST['species_selection'] == 'all') { echo 'selected="selected"';} elseif ($_GET['species'] == 'all') {echo 'selected="selected"';} else{echo '';}?>>all</option>
                <?php
                $query = "SELECT DISTINCT species from animaldashboard";
                $species_option = mysqli_query($db, $query);
                include ('lib/show_queries.php');
                if (mysqli_num_rows($result) == 0) {
                    array_push($error_msg,  "SELECT ERROR:Failed to find species ... <br>" . __FILE__ ." line:". __LINE__ );
                }
                while ($species_row = mysqli_fetch_array($species_option)) {
                    $species_value = "<option value='" . $species_row['species'] . "'";
                    if (!empty($_POST['species_selection']) && $_POST['species_selection'] == $species_row['species']) {
                        $species_value = $species_value . " selected='SELECTED' ";
                    }
                    elseif (!empty($spe) && $spe == $species_row['species']) {
                        $species_value = $species_value . " selected='SELECTED' ";
                    }
                    $species_value = $species_value .  ">" . $species_row['species'] . "</option>";
                    echo $species_value;
                }

                ?>     </select></tb>
        <tb>
            <select name = 'adoptability_selection' onchange="this.form.submit();" >
                <option value="all" <?php if (isset($_POST['adoptability_selection']) && $_POST['adoptability_selection'] == 'all') { echo 'selected="selected"';} elseif ($_GET['adopt'] == 'all') {echo 'selected="selected"';} else{echo '';} ?>>all</option>
                <option value="TRUE" <?php if (isset($_POST['adoptability_selection']) && $_POST['adoptability_selection'] == 'TRUE') { echo 'selected="selected"';} elseif ($_GET['adopt'] == 'TRUE') {echo 'selected="selected"';} else{echo '';}  ?>>Yes</option>
                <option value="FALSE"<?php if (isset($_POST['adoptability_selection']) && $_POST['adoptability_selection'] == 'FALSE') { echo 'selected="selected"';} elseif ($_GET['adopt'] == 'FALSE') {echo 'selected="selected"';} else{echo '';}  ?>>No</option>


                </select></tb>

        <tb>

    </tr></form>
    <table class="table table-bordered tablemain">
        <tr>
            <th>Name <a href="animal_dashboard.php?order=asc&by=name&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;
                } ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >
                    &#8679;
                </a> <a href="animal_dashboard.php?order=desc&by=name&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;
                } ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >
                    &#8681;
                </a> </th>
            <th>Species <a href="animal_dashboard.php?order=asc&by=species&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;
                } ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >
                    &#8679;
                </a> <a href="animal_dashboard.php?order=desc&by=species&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;
                } ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >
                    &#8681;
                </a></th>
            <th> Breed <a href="animal_dashboard.php?order=asc&by=breed&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8679;</a>
                <a href="animal_dashboard.php?order=desc&by=breed&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8681;</a></th>
            <th> Sex <a href="animal_dashboard.php?order=asc&by=sex&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8679;</a>
                <a href="animal_dashboard.php?order=desc&by=sex&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8681;</a></th>

            <th> Age <a href="animal_dashboard.php?order=asc&by=age&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8679;</a>
                <a href="animal_dashboard.php?order=desc&by=age&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8681;</a></th>
            <th> Alteration status <a href="animal_dashboard.php?order=asc&by=alteration_status&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8679;</a>
                <a href="animal_dashboard.php?order=desc&by=alteration_status&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8681;</a></th>
            <th> Adoptability  <a href="animal_dashboard.php?order=asc&by=adoptability_status&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8679;</a>
                <a href="animal_dashboard.php?order=desc&by=adoptability_status&species=<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['species_selection']);} else { echo $spe;} ?>&adopt=<?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST'){
                    echo mysqli_real_escape_string($db, $_POST['adoptability_selection'] ); } else { echo $adopt;} ?>" >&#8681;</a></th>
        </tr>
        <?php


            while ($row = mysqli_fetch_array($result)) {

                $pet_ID = $row['pet_id'];
                $name = $row['name'];
                $species = $row['species'];
                $breed = $row['breed'];
                $sex = $row['sex'];
                $alteration_status = $row['alteration_status'];
                if ($alteration_status == 'TRUE') {$alteration_status = 'Yes';} else {$alteration_status = 'No';}
                $age = $row['age'];
                if ($age <12 ) {$age = $age.'m'; } else {$age = floor($age/12) . 'y '. $age%12 . 'm';}
                $adoptability = $row['adoptability_status'];
                if ($adoptability == 'TRUE') {$adoptability = 'Yes';} else {$adoptability = 'No';}



                echo "
 <tr>
   <td><a href='animal_detail.php?pet_id=$pet_ID'>$name</a> </td>
   <td> $species </td>
    <td> $breed </td>
	 <td> $sex </td>
	 <td> $age </td>
	  <td> $alteration_status </td>
	   
	    <td> $adoptability </td>
 </tr>";


            }

        ?>
    </table>

    <br><br>


    <a href="add_adoption_application.php" class="btn btn-primary main-btn"> Add Adoption Application</a>

    <table class="tablemain1" style="background-color:rgba(255, 255, 255, 0.9);">
        <tr>
            <th colspan="3">Capacity (Available Spots)</th>
        </tr>
        <?php
        $query = "SELECT animaldashboard.species, COUNT(*) AS num, speciestypes.capacity 
                    FROM animaldashboard 
                    JOIN speciestypes on animaldashboard.species = speciestypes.species
                    GROUP BY species";
        $result = mysqli_query($db, $query);
        include ('lib/show_queries.php');
        if (mysqli_num_rows($result) == 0) {
            array_push($error_msg,  "SELECT ERROR:Failed to find species and capacity ... <br>" . __FILE__ ." line:". __LINE__ );
        }
		$visible = FALSE;

        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $pet_type = $row['species'];
            $num = $row['num'];
            $capacity = $row['capacity'];
            if ($capacity > $num ){
                $visible = TRUE;
            }


            echo "
            <tr>
            <td>  $pet_type </td>
            <td>" .($capacity - $num) . "</td>
            <td> $capacity</td>
            </tr>
            ";
        }

        if(in_array($_SESSION['employee_type'], array('manager', 'employee')) && $visible ){echo '
 <a href="add_animal.php" class=" btn btn-primary main-btn"> Add Animal</a>';
            }

        if(in_array($_SESSION['employee_type'], array('manager')) ){echo '
 <a href="adoption_application_review.php" class=" btn btn-primary main-btn"> adoption application review</a>';
        }

        ?>


    </table>



    <?php include("lib/error.php"); ?>
    <?php include("lib/footer.php"); ?>





</div>

</body>
</html>
