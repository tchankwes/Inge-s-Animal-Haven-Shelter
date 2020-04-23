<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee', 'volunteer')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}
$pet_id = htmlspecialchars($_GET['pet_id']);
$species = htmlspecialchars($_GET['species']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rowCount = count($_POST["vaccine_type"]);
    $confirmation = FALSE;
    if ($rowCount > 0) {
        for ($i = 1; $i < $rowCount; $i++) {
            $pet_id = htmlspecialchars($_GET['pet_id']);
            $vaccination_type = $_POST['vaccine_type'][$i];
            $date = $_POST['date'][$i];
            $expiration_date = $_POST['exp_date'][$i];
            $vaccine_number = $_POST['vaccination_number'][$i];
            $username = $_SESSION['username'];
            $query = "INSERT INTO vaccinationadministered
                    (pet_id, vaccination_type, date_administered, expiration_date, username, vaccination_number)
                    VALUES
                    ('$pet_id', '$vaccination_type', '$date', '$expiration_date', '$username', '$vaccine_number')";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "Insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );
            }
            else {
                $confirmation = TRUE;
            }
        }
    }
}
?>

<?php
//include("lib/header.php"); ?>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script>
    $(document).ready(function(){
        $(".add-row").click(function(){
            var add_vaccine = true;
            var vaccine_type = $("#vaccine_type").val();
            var date = $("#date").val();
            var exp_date = $("#exp_date").val();
            var vaccination_number = $("#vaccination_number").val();
            if (vaccine_type == ""){
                vaccine_type_err.innerHTML = "<font color='red'>please select a vaccine</font>";
                add_vaccine = false;}
            if (date == ""){
                date_err.innerHTML = "<font color='red'>please select a date</font>";
                add_vaccine = false;}
            if (exp_date == ""){
                exp_date_err.innerHTML = "<font color='red'>please select exp date</font>";
                add_vaccine = false;}
            if (add_vaccine){
                var markup = "<tr><td><input type='checkbox' name='record'></td>" +
                    "<td><input type='text' name='vaccine_type[]' value='" + vaccine_type + "'></td>" +
                    "<td><input type='text' name='date[]' value='" + date + "'></td>" +
                    "<td><input type='text' name='exp_date[]' value='" + exp_date + "'></td>" +
                    "<td><input type='text' name='vaccination_number[]' value='" + vaccination_number + "'></td></tr>"
                ;
                $("table tbody").append(markup);
                this.form.reset();
                vaccine_type_err.innerHTML = "*required";
                date_err.innerHTML = "*required";
                exp_date_err.innerHTML = "*required"
            }});
        // Find and remove selected table rows
        $(".delete-row").click(function(){
            $("table tbody").find('input[name="record"]').each(function(){
                if($(this).is(":checked")){
                    $(this).parents("tr").remove();
                }
            });
        });
    });
</script>
<head>
    <title>Add Vaccination</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body style=" background-image:url(img/2.jpg); background-attachment:fixed;">
<?php include("lib/menu.php"); ?>
<div class="main"><br><br><br>
    <center><h2 style="font-weight:bold; color:#FFFFFF; text-shadow: 2px 2px 5px red;">
            ADD VACCINATION</h2></center>
    <br>
    <br><br>
    <div id="main_container">
        <!--    --><?php //include("lib/menu.php"); ?>
        <div class="center_content">



            <form name="add_vaccination_form" id="add_vaccination_form" method="POST">

                    <tr>
                        <td class="item_label addanitab">Vaccine Type</td>
                        <td>
                            <select id="vaccine_type" name="vaccine_type[]">
                                <option value="">Select...</option>
                                <?php
                                echo $species;
                                $query = "SELECT vaccination_type
                                    FROM vaccinationtypes
                                    WHERE species = '$species'
                                    AND vaccination_type NOT IN (SELECT vaccination_type
                                                          FROM vaccinationadministered
                                                          WHERE pet_id = '$pet_id'
                                                          AND expiration_date >= CURRENT_DATE())";
                                $result = mysqli_query($db, $query);
                                include ('lib/show_queries.php');
                                if (mysqli_affected_rows($db) == -1) {
                                    array_push($error_msg, "SELECT ERROR: Failed to find vaccine types ... <br>" . __FILE__ ." line:". __LINE__ );
                                }
                                while ($vaccine_type_row = mysqli_fetch_array($result)) {
                                    $option_value = "<option value='" . $vaccine_type_row['vaccination_type'] . "'";
                                    if (!empty($_POST['vaccine_type']) && $_POST['vaccine_type'] == $vaccine_type_row['vaccination_type']) {
                                        $option_value = $option_value . " selected='SELECTED' ";
                                    }
                                    $option_value = $option_value .  ">" . $vaccine_type_row['vaccination_type'] . "</option>";
                                    echo $option_value;
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <br>
                    <tr>

                        <td class="item_label addanitab">Date</td>
                        <td><input type="date" id="date" name="date[]" value="<?php
                            if(empty($date) || !isset($date)) {echo date('Y-m-d');} else {echo $date;} ?>" />
                        </td>
                    </tr>
                    <br>

                    <tr>
                        <td class="item_label addanitab">Expiration date</td>
                        <td><input type="date" id="exp_date" name="exp_date[]" value="<?php
                            if(empty($exp_date) || !isset($exp_date)) {echo date('Y-m-d');} else {echo $exp_date;} ?>" />
                        </td>
                    </tr>

                    <br>

                    <tr>
                        <td class="item_label addanitab">Vaccination Number</td>
                        <td><input type="text" id="vaccination_number" name="vaccination_number[]" value="" >
                        </td>
                    </tr>
                    <br>

                    <input type="button" class="btn btn-primary add-row" value="Add Vaccination">
                    <br>
                    <table style="background-color:rgba(255, 255, 255, 0.9);">
                        <br><br>

                        <thead>
                        <tr>
                            <th>Select</th>
                            <th>Vaccination Type</th>
                            <th>Date</th>
                            <th>Expiration date</th>
                            <th>Vaccination number</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                        </tr>
                        </tbody>
                    </table>
                    <td><button type="button" class="btn btn-danger delete-row">Delete Row</button></td>
                    <button type="submit" formmethod="post" class="btn btn-primary">Submit</button>


            </form>

            <?php
            if  ($_SERVER['REQUEST_METHOD'] == 'POST' && $confirmation) {
                print "<div class='subtitle'>Vaccination added successfully!</div>";
            }
            ?>



            <?php include("lib/error.php"); ?>
            <div class="clear"></div>
        </div>
        <?php include("lib/footer.php"); ?>
    </div>

</body>
</html>
