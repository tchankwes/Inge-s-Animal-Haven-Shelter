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

$pet_id = $_GET['pet_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_id = $_GET['pet_id'];
    $new_sex = mysqli_real_escape_string($db, $_POST['new_sex']);
    $new_microchip_id = mysqli_real_escape_string($db, $_POST['new_micid']);
    $new_alteration = mysqli_real_escape_string($db, $_POST['new_altsta']);
    $new_breed = mysqli_real_escape_string($db, $_POST['new_breed']);
    $old_sex = mysqli_real_escape_string($db, $_POST['old_sex']);
    echo 'pet_id='.$new_breed.'<br>';


        if (!empty($new_sex)) {
            $query = "UPDATE animals
              SET sex  = '$new_sex'
              WHERE pet_id = '$pet_id'";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "sex update failed ... <br>" . __FILE__ ." line:". __LINE__ );
            }
        }
        if (!empty($new_alteration && $new_alteration != 'Select Any Option') ) {
            $query = "UPDATE animals
              SET alteration_status = '$new_alteration'
              WHERE pet_id = '$pet_id'";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "new alter update failed ... <br>" . __FILE__ ." line:". __LINE__ );
            }

        }
        if (!empty($new_microchip_id)) {
            $query = "UPDATE animals
              SET  microchip_id = '$new_microchip_id'
              WHERE pet_id = '$pet_id'";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "new chip update failed ... <br>" . __FILE__ ." line:". __LINE__ );
            }
        }


        $query_ready = TRUE;

        if (in_array('Unknown', $_POST['new_breed']) || in_array('Mixed', $_POST['new_breed'])) {
            if (count($_POST['new_breed']) > 1) {
                $breed_error = 'Unknown/Mixed can not be selected with other breed';
                $query_ready = FALSE;
            }
        }



        if ($query_ready) {

            $query = "DELETE FROM breeds WHERE pet_id = '$pet_id' and (breed = 'Unknown' or breed = 'Mixed')";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "delete breed failed ... <br>" . __FILE__ . " line:" . __LINE__);
            } else {

                $rowCount = count($_POST["new_breed"]);

                if ($rowCount > 0) {

                    for ($i = 0; $i < $rowCount; $i++) {

                        $breed = $_POST['new_breed'][$i];
                        $query = "INSERT INTO breeds
                            (pet_id, breed)
                            VALUES
                            ('$pet_id', '$breed')";


                        $result = mysqli_query($db, $query);

                        include('lib/show_queries.php');
                        if (mysqli_affected_rows($db) == -1) {
                            array_push($error_msg, "Insertion failed ... <br>" . __FILE__ . " line:" . __LINE__);
                        } else {




                        }

                    }
                }
            }
        }





        }




?>
<html>
 <head>

  <title>Animal Details</title>
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
ANIMAL DETALS</h2></center>												   

<br>
<br><br>

     <form action="animal_detail.php?pet_id=<?php echo $pet_id?>"  method="post">
 <div class="animain">
 <div class="anileft">
 <?php

 #require_once 'config.php';
$id=$_GET['pet_id'];
$query="SELECT animals.pet_id, name,species, breed, sex, alteration_status, age, description, microchip_id, surrender_date, surrender_reason, surrender_username, brought_by_animal_control
FROM animals
LEFT JOIN ( SELECT GROUP_CONCAT(breed SEPARATOR '/') as breed, pet_ID 
          FROM breeds 
          GROUP BY pet_ID) AS breeds
ON animals.pet_ID = breeds.pet_ID
WHERE animals.pet_id = '$id' ";
$pri=mysqli_query($db, $query);
 include('lib/show_queries.php');
 if (mysqli_num_rows($pri) == 0) {
     array_push($error_msg, "Failed to find adoption applications to review ... <br>" . __FILE__ ." line:". __LINE__ );
 }

$row=mysqli_fetch_array($pri);
$age=$row['age'];
 if ($age <12 ) {$age = $age.'m'; } else {$age = floor($age/12) . 'y '. $age%12 . 'm';};
$alteration_status=$row['alteration_status'];
$microchip_ID=$row['microchip_id'];
$sex=$row['sex'];
$name=$row['name'];
$description=$row['description'];
$species=$row['species'];
$breed=$row['breed'];
$surrender_date=$row['surrender_date'];
$surrender_username=$row['surrender_username'];
$surrender_reason=$row['surrender_reason'];
$brought_by_animal_control=$row['brought_by_animal_control'];
if($alteration_status==1){
$alteration_status="Yes";
}
else {
$alteration_status="No";
}
 if($brought_by_animal_control==1){
     $brought_by_animal_control="Yes";
 }
 else {
     $brought_by_animal_control="No";
 }
 
 ?>

 <table style="background-color:rgba(255, 255, 255, 0.9);">
     <tr>
         <td class="addanitab"> Pet ID </td>
         <td class="addanitxt"><?php echo $id; ?></td>
     </tr>
 <tr>
 <td class="addanitab"> Name </td>
    <td class="addanitxt"> <?php echo $name; ?> </td>
 </tr>
 <tr>
 
   <td class="addanitab"> Species </td>
    <td class="addanitxt"> <?php echo $species; ?> </td>
 </tr>
 
 <tr>
 <td class="addanitab"> Breed <?php if (!empty($breed_error)) {echo $breed_error;} ?></td>
<!--     <td class="addanitxt">  --><?php //echo $breed; ?><!-- </td>-->
     <?php if ($breed == 'Unknown' || $breed == 'Mixed'){
        $option = '<td class="addanitxt"><select name="new_breed[]" class="addanitxt" style="border:none;" multiple>';
        $query="select breed from speciesbreed WHERE species = '$species'";
        $result = mysqli_query($db, $query);
         include('lib/show_queries.php');
         if (mysqli_num_rows($result) == 0) {
             array_push($error_msg, "Failed to find breed ... <br>" . __FILE__ ." line:". __LINE__ );
         }
        while ($row = mysqli_fetch_array($result)) {
            $breed_name=$row['breed'];
            $option = $option. "<option value='" .$breed_name . "'";
            if (!empty($new_breed) && $new_breed == $breed_name) {
                $option = $option . "selected = 'SELECTED'";
            }
            $option = $option . ">" . $breed_name . "</option>";}
        $option = $option . "</select></td>";
        echo $option;}

        else {
            echo '<td class="addanitxt">'  . $breed.  " </td>";
        }
        ?>

 </tr>
 <tr>
 <td class="addanitab"> Sex </td>

    <td class="addanitxt">

        <?php if($sex == 'unknown')
        {
            echo '<select class="addanitxt" style="border:none;" name="new_sex">
                                <option> Select Any Option </option>
                                <option value="male"> Male </option>
                                <option value="female"> Female </option>
                                <option value="unknown"> Unknown </option>
                            </select>';
        }
        else {echo $sex;}
        ?>


    </td>
 </tr>
 <tr>
 <td class="addanitab"> Alteration Status </td>
    <td class="addanitxt">  <?php
        if($alteration_status == 'No') {
            echo '<select class="addanitxt" style="border:none;" name="new_altsta" >
                                <option> Select Any Option </option>
                                <option value="1"> Yes </option>
                                <option value="0"> No </option>
                            </select>';
        }
        else{
        echo $alteration_status;} ?>
    </td>
 </tr>
 <tr>
 <td class="addanitab"> Age </td>
    <td class="addanitxt"> <?php echo $age; ?> </td>
 </tr>
 <tr>
 <td class="addanitab"> Microchip ID </td>
    <td class="addanitxt"><?php
        if ($microchip_ID == '') {
            echo '<input type="text" name="new_micid" class="addanitxt" style="border:none;">';
        }
        else {echo $microchip_ID;}

        ?></td>
 </tr>

 <tr>
 <td class="addanitab"> Surrender Date </td>
    <td class="addanitxt"> <?php echo $surrender_date; ?> </td>
 </tr>
     <tr>
         <td class="addanitab"> Surrender Username </td>
         <td class="addanitxt"> <?php echo $surrender_username; ?> </td>
     </tr>
 <tr>
 <td class="addanitab"> Surrender by AC </td>
    <td class="addanitxt"> <?php echo $brought_by_animal_control; ?></td>
 </tr> 
 </table>
 </div>


 
 <div class="aniright">

 <table>
  <tr>
 <td style="width:250px; background-color:#FFD966; font-weight:bold;"> Description </td>
 </tr>
 <tr>
 <td style="width:330px; background-color:rgba(255, 255, 255, 0.9);  height:100px;"> <?php echo $description; ?> </td>
 </tr>
 
 </table>

 <br><br><br><br>

 <table>
  <tr>
 <td style="width:250px; background-color:#FFD966; font-weight:bold;"> Surrender Reason </td>
 </tr>
 <tr>
 <td style="width:330px; background-color:rgba(255, 255, 255, 0.9); height:100px;"> <?php echo $surrender_reason; ?> </td>
 </tr>
 
 </table>

 </div>
 </div>
     <?php
         if ($sex == 'unknown' || $microchip_ID == '' || $breed == 'Unknown' || $breed == 'Mixed' || $alteration_status == 0) {
         echo' <input type="submit" class="main-btn-center" value="MODIFY" name="sub"> ';}


         ?>
     </form>
 <center> <?php
// $id=$_GET['pid'];
 // If the employee type is NOT manager or employee, then adoption button SHOULD BE HIDDEN. Also, change the php page from applicationform.php

//     if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee'))))
//     {


         $query = "SELECT * FROM animaldashboard WHERE pet_id = '$id'";
         $result = mysqli_query($db, $query);
     include('lib/show_queries.php');
     if (mysqli_num_rows($result) == 0) {
         array_push($error_msg, "Failed to retrieve information form animal_dashboard ... <br>" . __FILE__ ." line:". __LINE__ );
     }
         if (mysqli_num_rows($result) > 0){
             $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
             $adoptability = $row['adoptability_status'];
             echo '<a href="add_vaccination.php?pet_id='.$id.'&species=' .$species. '" class="main-btn-center"> Add Vaccination </a>';
             if ($adoptability == 'TRUE' && in_array($_SESSION['employee_type'], array('manager', 'employee'))) {
                 echo' <a href="adopter_lookup.php?pet_id='.$id.'" class="main-btn-center"> Add Adoption </a> ';
             }
         }

  ?>
 </center>
<br>


<table class="tablemain">
<tr>
<th colspan="7"> VACCINATIONS </th>
</tr>
<tr>
<td rowspan="2" class="anid"> # </td>
<td rowspan="2" class="anid"> Vaccine type </td>
<td rowspan="2" class="anid"> Required for adoption </td>
    <td rowspan="2" class="anid"> Username </td>
<td colspan="4" style="text-align:center;" class="anid"> Administer? </td>
</tr>
<tr>
<td class="anid"> Date Administered </td>
<td class="anid"> Expiration Date </td>
<td class="anid"> Vaccination Number </td>
</tr>

 <?php
$id=$_GET['pet_id'];

$query="select * from animals where pet_ID='$id'";
$pri=mysqli_query($db, $query);
$row2=mysqli_fetch_array($pri);
$species=$row2['species'];

$n=1;
$query1="SELECT vaccinationadministered.vaccination_type, date_administered, expiration_date, username,
	   CASE WHEN required = 1 THEN 'Required' ELSE 'Not Required' END as required, vaccination_number
FROM vaccinationadministered
LEFT JOIN (SELECT vaccination_type, required 
        FROM vaccinationtypes WHERE species = '$species' ) AS vaccinationtypes
ON vaccinationadministered.vaccination_type = vaccinationtypes.vaccination_type
WHERE pet_id = '$id' 
ORDER BY vaccinationadministered.vaccination_type, date_administered";

$pri1=mysqli_query($db, $query1);
 include('lib/show_queries.php');
 if (mysqli_num_rows($pri1) == 0) {
     array_push($error_msg, "Failed to find adoption applications to review ... <br>" . __FILE__ ." line:". __LINE__ );
 }

while($row1=mysqli_fetch_array($pri1)){
$pet_ID=$row1['pet_ID'];
$required=$row1['required'];
$vaccination_type=$row1['vaccination_type'];
$username=$row1['username'];
$date_administered=$row1['date_administered'];
$expiration_date=$row1['expiration_date'];
$vaccination_number=$row1['vaccination_number'];

echo "
<tr>
<td> $n </td>
<td> $vaccination_type </td>
<td> $required </td>
<td> $username </td>
<td> $date_administered </td>
<td> $expiration_date </td>
<td> $vaccination_number </td>
</tr>
";
$n++;
}
 ?>
 </table>
 
 
 
 </div>
 <?php include("lib/error.php"); ?>
				
   </body>
</html>
