<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee', 'volunteer')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}
$email = $first_name = $last_name = $co_first_name = $co_last_name = $phone = $street = $city = $state = $zip = $email_message= "";
$record_found = FALSE;
$inserted = FALSE;
$email_message = '';
$state_array = array(
    "AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC",
    "DE", "FL", "GA", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA",
    "MA", "MD", "ME", "MI", "MN", "MO", "MS", "MT", "NC", "ND", "NE",
    "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "RI", "SC",
    "SD", "TN", "TX", "UT", "VA", "VT", "WA", "WI", "WV", "WY");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['first_name']) && empty ($_POST['last_name'])) {
        if (empty($_POST['email'])) {
            $email_err = "please input email";
        }
        elseif (!empty($_POST["email"]) && !filter_var(mysqli_real_escape_string($db, $_POST['email']), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Email is not valid format";
        }
        else {
            $email = mysqli_real_escape_string($db, $_POST['email']);
            $query = "SELECT * FROM adopter 
                        WHERE email_address = '$email'";
            $result = mysqli_query($db, $query);
//            echo (mysqli_affected_rows($db));
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == 0) {
                $email_message = "No match found. Please add new record";
            }
            else {
                $email_message = "adopter found";
                $record_found = TRUE;
                $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
            }
        }
    }
    else {
        $query_ready = TRUE;
        if (empty($_POST['email'])) {
            $query_ready = FALSE;
            $email_err = "Email is required";
        }
        elseif (!filter_var(mysqli_real_escape_string($db, $_POST['email']), FILTER_VALIDATE_EMAIL)) {
            $query_ready = FALSE;
            $email_err = "Email is not valid format";
        }
        else {
            $email = mysqli_real_escape_string($db, $_POST['email']);
        }
        if (empty($_POST['app_date'])) {
            $query_ready = FALSE;
            $app_date_err = "Date is required";
        }
        else {
            $app_date= mysqli_real_escape_string($db, $_POST['app_date']);
        }
        if (empty($_POST['first_name'])) {
            $query_ready = FALSE;
            $first_name_err = "First name is required";
        }
        else {
            $first_name= mysqli_real_escape_string($db, $_POST['first_name']);
        }
        if (empty($_POST['last_name'])) {
            $query_ready = FALSE;
            $last_name_err = "Last name is required";
        }
        else {
            $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
        }
        if (empty($_POST['street'])) {
            $query_ready = FALSE;
            $street_err = "Street is required";
        }
        else {
            $street = mysqli_real_escape_string($db, $_POST['street']);
        }
        if (empty($_POST['zip'])) {
            $query_ready = FALSE;
            $zip_err = "Zip code is required";
        }
        else {
            $zip = mysqli_real_escape_string($db, $_POST['zip']);
            if (!(ctype_digit($zip)) || strlen($zip) != 5) {
                $query_ready = FALSE;
                $zip_err = "Zip code must be 5 digits and number only";
            }
        }
        if (empty($_POST['city'])) {
            $query_ready = FALSE;
            $city_err = "City is required";
        }
        else {
            $city = mysqli_real_escape_string($db, $_POST['city']);
        }
        if (empty($_POST['state'])) {
            $query_ready = FALSE;
            $state_err = "State is required";
        }
        else {
            $state = mysqli_real_escape_string($db, $_POST['state']);
        }
        if (empty($_POST['phone'])) {
            $query_ready = FALSE;
            $phone_err = "Phone number is required";
        }
        else {
            $phone = mysqli_real_escape_string($db, $_POST['phone']);
            if (!(ctype_digit($phone)) || strlen($phone) != 10) {
                $query_ready = FALSE;
                $phone_err = "Phone number must be 10 digits and number only";
            }
        }
        $app_date = mysqli_real_escape_string($db, $_POST['app_date']);
        if($query_ready) {
            if (!$record_found) {
                $query = "SELECT * FROM adopter WHERE  email_address = '$email'";
                $result = mysqli_query($db, $query);
                if (mysqli_num_rows($result) > 0) {
                    $email_message = 'adopter exists. ';
                }
                else {
                    $query = "INSERT INTO adopter(applicant_first_name, applicant_last_name, street, city, state, zip, phone_number, email_address)
                                VALUES ('$first_name', '$last_name', '$street', '$city', '$state', '$zip', '$phone', '$email')";
                    $result = mysqli_query($db, $query);
                    include ('lib/show_queries.php');
                    if (mysqli_affected_rows($db) == -1) {
                        array_push($error_msg, "adopter insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );
                    }
                    else {
                        print "<div class='subtitle'><adopter inserted.></div>";
                    }
                }
            }
            $query = "INSERT INTO AdoptionApplication (date_of_application, email_address)
                        VALUES ('$app_date', '$email')";
            $result = mysqli_query($db, $query);
            include ('lib/show_queries.php');
            if (mysqli_affected_rows($db) == -1) {
                array_push($error_msg, "application insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );
            }
            else {
                $inserted = TRUE;
                $record_found = FALSE;
                $app_id = mysqli_insert_id($db);
            }
            if (!empty($_POST['co_first_name']) && !empty($_POST['co_last_name'])) {
                $co_first_name = mysqli_real_escape_string($db, $_POST['co_first_name']);
                $co_last_name = mysqli_real_escape_string($db, $_POST['co_last_name']);
                $query = "INSERT INTO coadopter (applicant_number, email_address, co_applicant_first_name, co_applicant_last_name)
                            VALUES ('$app_id', '$email', '$co_first_name', '$co_last_name')";
                $result = mysqli_query($db, $query);
                include ('lib/show_queries.php');
                if (mysqli_affected_rows($db) == -1) {
                    array_push($error_msg, "co_adopter insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );
                }
            }
        }
    }
}
//include("lib/header.php");
?>

<title>Add adoption application</title>
  <link rel="stylesheet" href="css/bootstrap.css">
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
  <!-- CSS -->
 <link rel="stylesheet" type="text/css" href="css/style.css">
  <!-- IE-only CSS -->
</head>
 <body style=" background-image:url(img/2.jpg); background-attachment:fixed;">
 <?php include("lib/menu.php"); ?>
 <div class="main"><br>
 <center><h2 style="font-weight:bold; color:#FFFFFF; text-shadow: 2px 2px 5px red;">
ADD ADOPTION APPLICATION</h2></center>
<br>
<div class="container">
    <?php #include("lib/menu.php"); ?>
    <div class="center_content">
        <div class="subtitle">Add adoption application</div>
        <form name="adoption_form" action="add_adoption_application.php" method="POST">
            <table>
                <tr>
                    <td class="item_label addanitab">Email address</td>
                    <td><input type="text" name="email" value="<?php if ($inserted){echo '';} else {echo $_POST['email'];} ?>" />
                        <span class="error_msg">* <?php echo $email_err; echo $email_message; ?></span>
                        <a href="javascript:adoption_form.submit();" class="btn btn-primary fancy_button"><span class="glyphicon glyphicon-search"></span >&nbsp;Check</a></td>
                </tr>

                <tr>
                    <td class="item_label addanitab">date</td>
                    <td><input type="date" name="app_date" max="<?php echo date('Y-m-d') ?>" value="<?php if ($record_found) {echo $row['app_date'];} elseif ($inserted){echo '';} else {echo $_POST['app_date'];} ?>" />
                        <span class="error_msg">* <?php echo $app_date_err;?></span></td>
                </tr>


                <tr>
                    <td class="item_label addanitab">First name</td>
                    <td><input type="text" name="first_name" value="<?php if ($record_found) {echo $row['applicant_first_name'];} elseif ($inserted){echo '';} else {echo $_POST['first_name'];} ?>" />
                        <span class="error_msg">* <?php echo $first_name_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Last name</td>
                    <td><input type="text" name="last_name" value="<?php if ($record_found) {echo $row['applicant_last_name'];} elseif ($inserted){echo '';} else {echo $_POST['last_name'];} ?>" />
                        <span class="error_msg">* <?php echo $last_name_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Co applicant first name</td>
                    <td><input type="text" name="co_first_name" value="<?php if($inserted) {echo '';} else {echo $_POST['co_first_name'];} ?>" /></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Co applicant last name</td>
                    <td><input type="text" name="co_last_name" value="<?php if($inserted) {echo '';} else {echo $_POST['co_last_name'];} ?>" /></td>
                </tr>

                <tr>
                    <td class="item_label addanitab">Street</td>
                    <td><input type="text" name="street" value="<?php if ($record_found) { echo $row['street'];} elseif ($inserted){echo '';} else {echo $_POST['street'];} ?>" />
                        <span class="error_msg">* <?php echo $state_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">City</td>
                    <td><input type="text" name="city" value="<?php if ($record_found) {echo $row['city'];} elseif ($inserted){echo '';} else {echo $_POST['city'];} ?>" />
                        <span class="error_msg">* <?php echo $city_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">State</td>
                    <td>
                        <select id="SelectState" name="state">
                            <option value=''>Select...</option>
                            <?php foreach($state_array as $state_name) {
                                echo $state_name;
                                $option_value = "<option value='" . $state_name . "'";
                                if ($record_found && $state_name == $row['state']) {
                                    $option_value = $option_value . " selected='SELECTED' ";
                                }
//                                elseif ($state_name = $_POST['state']){
//                                    $option_value = $option_value . " selected='SELECTED' ";
//                                }
                                $option_value = $option_value .  ">" . $state_name. "</option>";
                                echo $option_value;
                            }
                            ?>
                        </select>
                        <!--                        <input type="text" name="state" value="--><?php //if ($record_found) { echo $row['state'];} elseif ($inserted){echo '';} else {echo $_POST['state'];} ?><!--" />-->
                        <span class="error_msg">* <?php echo $state_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Zip code</td>
                    <td><input type="text" name="zip" value="<?php if ($record_found) {echo $row['zip'];} elseif ($inserted){echo '';} else {echo $_POST['zip'];} ?>" />
                        <span class="error_msg">* <?php echo $zip_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Phone number</td>
                    <td><input type="text" name="phone" value="<?php if ($record_found) {echo $row['phone_number'];} elseif ($inserted){echo '';} else {echo $_POST['phone'];} ?>" />
                        <span class="error_msg">* <?php echo $phone_err;?></span></td>
                </tr>


            </table>

        </form>
        <a href="javascript:adoption_form.submit();" class="btn btn-primary fancy_button">Submit</a>
    </div>



    <div class='subtitle'><?php if ($inserted) {echo ("Application number " . $app_id . " is assigned."); } ?></div>


    <?php include("lib/error.php"); ?>
    <?php include("lib/footer.php"); ?>




</div>
</div>
</body>
</html>