<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}
include ('lib/common.php');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $add_v = $_POST['add_v'];
    $name = mysqli_real_escape_string($db, $_POST['name']);
    $species = mysqli_real_escape_string($db, $_POST['species']);
    $breed =$_POST['breed'];
    $sex= mysqli_real_escape_string($db,$_POST['sex']);
    $altsta= mysqli_real_escape_string($db,$_POST['altsta']);
    $age= mysqli_real_escape_string($db, $_POST['age']);
    $micid=mysqli_real_escape_string($db, $_POST['micid']);
    $surrdate=mysqli_real_escape_string($db, $_POST['surrdate']);
    $surrrea=mysqli_real_escape_string($db, $_POST['surrrea']);
    $surrac=mysqli_real_escape_string($db, $_POST['surrac']);
    $dis=mysqli_real_escape_string($db, $_POST['dis']);
    $user = $_SESSION['username'];
    $query_ready = TRUE;
    if(in_array('Unknown', $_POST['breed']) || in_array('Mixed', $_POST['breed'])) {
        if (count($_POST['breed']) > 1) {
            $breed_error = 'Unknown/Mixed can not be selected with other breed';
            $query_ready = FALSE;
        }
    }
    if ($query_ready) {
        $query = "insert into animals
                    (age,alteration_status,microchip_ID,sex,name,description,species,surrender_date,surrender_username,surrender_reason,brought_by_animal_control)
                    values ('$age','$altsta','$micid','$sex','$name','$dis','$species','$surrdate','$user','$surrrea','$surrac')";
        $result = mysqli_query($db, $query);
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "animal insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );
        }
        else {

            $pet_id = mysqli_insert_id($db);
            $rowCount = count($_POST["breed"]);
            if ($rowCount > 0) {
                $inserted = TRUE;
                for ($i = 0; $i < $rowCount; $i++) {
                    $breed = $_POST['breed'][$i];
                    $query = "INSERT INTO breeds
                        (pet_id, breed)
                        VALUES
                        ('$pet_id', '$breed')";
                    $result = mysqli_query($db, $query);
                    include('lib/show_queries.php');
                    if (mysqli_affected_rows($db) == -1) {
                        $inserted = FALSE;
                        array_push($error_msg, "Insertion failed ... <br>" . __FILE__ . " line:" . __LINE__);
                    }
                }

                 if ($inserted && $add_v == 'TRUE') {
                            $_POST = array();
                            header(REFRESH_TIME . "url=add_vaccination.php?pet_id=".$pet_id."&species=". $species);
                        }
                    }
                }
            }


}
?>

<html>
<head>

    <title>Add Animal</title>
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
            ADD ANIMAL</h2></center>



    <br>




    <br><br>
    <?php
    ?>

    <br>
    <form action="add_animal.php" method="post">
        <div class="animain">
            <div class="anileft">
                <table>


                    <tr>
                        <td class="addanitab"> Name *</td>
                        <td class="addanitxt"> <input type="text" name="name" class="addanitxt" value="<?php if (!empty($_POST['name'])){echo $_POST['name'];} elseif($inserted){echo '';}else {echo '';} ?>" style="border:none;" required></td>
                    </tr>
                    <tr>

                        <td class="addanitab"> Species *</td>
                        <td class="addanitxt">
                            <select name="species" class="addanitxt" onchange="this.form.submit()" style="border:none;" required>
                                <option value=""> Select Any Option </option>

                                <?php
                                //                                require_once 'config.php';
                                $query="select * from speciestypes";
                                $pri=mysqli_query($db, $query);
                                while($row=mysqli_fetch_array($pri)){
                                    $cat_name=$row['species'];
                                    $option =  '<option value="'.$cat_name.'">'.$cat_name.'</option>';
                                    $option = "<option value='" .$cat_name. "'";
                                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $species == $cat_name && !$inserted) {
                                        $option = $option . "selected = 'SELECTED' ";
                                    }
                                    $option = $option . ">" . $cat_name. "</option>";
                                    echo $option;
                                }
                                ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="addanitab"> Breed *<?php if (!empty($breed_error)) {echo $breed_error;} ?></td>
                        <td class="addanitxt">
                            <select name="breed[]" class="addanitxt" style="border:none;" multiple required>
                                <option> Select breed </option>

                                <?php
                                //                                require_once 'config.php';
                                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                                    $query="select breed from speciesbreed WHERE species = '$species'";
                                    $pri=mysqli_query($db, $query);
                                    while($row=mysqli_fetch_array($pri)){
                                        $breed_name=$row['breed'];
                                        $option = "<option value='" .$breed_name. "'";
                                        if (!empty($breed) && $breed == $breed_name ) {
                                            $option = $option . "selected = 'SELECTED' ";
                                        }
                                        $option = $option . ">" . $breed_name. "</option>";
                                        echo $option;
                                    }
                                }
                                ?>
                            </select>

                        </td>


                    </tr>
                    <tr>
                        <td class="addanitab"> Sex *</td>
                        <td class="addanitxt">
                            <select class="addanitxt" style="border:none;" name="sex" required>
                                <option value=""> Select Any Option </option>
                                <option value="male"> Male </option>
                                <option value="female"> Female </option>
                                <option value="unknown"> Unknown </option>
                            </select>

                        </td>
                    </tr>
                    <tr>
                        <td class="addanitab"> Alteration Status *</td>
                        <td class="addanitxt">
                            <select class="addanitxt" style="border:none;" name="altsta" required>
                                <option value=""> Select Any Option </option>
                                <option value="1"> Yes </option>
                                <option value="0"> No </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="addanitab"> Age *</td>
                        <td class="addanitxt"> <input type="number" min="0" name="age" class="addanitxt" style="border:none;" required> </td>
                    </tr>
                    <tr>
                        <td class="addanitab"> Microchip id </td>
                        <td class="addanitxt"> <input type="text" name="micid" class="addanitxt" style="border:none;"> </td>
                    </tr>
                    <tr>
                        <td class="addanitab"> Surrender Date *</td>
                        <td class="addanitxt"> <input type="date" name="surrdate" max = "<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" class="addanitxt" style="border:none;" required> </td>
                    </tr>
                    <!--                    <tr>-->
                    <!--                        <td class="addanitab"> Surrender Reason </td>-->
                    <!--                        <td class="addanitxt"> <input type="text" name="surrrea1" class="addanitxt" style="border:none;" required> </td>-->
                    <!--                    </tr>-->
                    <tr>
                        <td class="addanitab"> Surrender by AC *</td>
                        <td class="addanitxt"> <select class="addanitxt" style="border:none;" name="surrac" required>
                                <option value=""> Select Any Option </option>
                                <option value="1"> Yes </option>
                                <option value="0"> No </option>
                            </select> </td>
                    </tr>
                </table>
            </div>

            <div class="aniright">
                <table>
                    <tr>
                        <td style="width:250px; background-color:#FFD966; font-weight:bold;"> Description *</td>
                    </tr>
                    <tr>
                        <td style="width:250px; height:100px;"> <textarea rows="6" cols="40" style=" border:none;" name="dis" required> </textarea> </td>
                    </tr>

                </table>
                <br><br><br><br>

                <table>
                    <tr>
                        <td style="width:250px; background-color:#FFD966; font-weight:bold;"> Surrender Reason *</td>
                    </tr>
                    <tr>
                        <td style="width:250px; height:100px;"> <textarea rows="6" cols="40" style=" border:none;" name="surrrea" required> </textarea> </td>
                    </tr>

                </table>

                <table>
                    <tr>
                        <td class="addanitab"> Add vacination after submit? *</td>
                        <td class="addanitab"><input type="radio" id="yes" name="add_v" value=TRUE required><label for="yes">Yes</label>
                            <input type="radio" id="no" name="add_v" value=FALSE><label for="no">No</label>
                        </td>


                    </tr>
                </table>



            </div>
        </div>
        <center>
            <input type="submit" class="main-btn-center" value="SUBMIT" name="sub">
        </center>

    </form>





</div>
<?php include("lib/error.php"); ?>

</body>
</html>