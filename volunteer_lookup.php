<?php
include('lib/common.php');
if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

/* if form was submitted, then execute query to search for adopter */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $query_ready = TRUE;

    if (empty($_POST["first_name_input"]) && empty($_POST["last_name_input"]))  {
        $query_ready = FALSE;
        $name_input_err_first = "Please input first or last name";
    } else {

        $first_name_input = $_POST["first_name_input"];
        $last_name_input = $_POST["last_name_input"];
    }

    if ($query_ready) {

        if (empty($_POST["last_name_input"]) ) {
        $query = "SELECT first_name, last_name, email, phone_number
                  FROM Volunteer
                  LEFT JOIN User
                  ON Volunteer.username = User.username
                  WHERE first_name LIKE '%$first_name_input%' 
                  ORDER BY last_name ASC, first_name ASC";
         } elseif (empty($_POST["first_name_input"]) ) {
        $query = "SELECT first_name, last_name, email, phone_number
                  FROM Volunteer
                  LEFT JOIN User
                  ON Volunteer.username = User.username
                  WHERE last_name LIKE '%$last_name_input%' 
                  ORDER BY last_name ASC, first_name ASC";
         } else {
        $query = "SELECT first_name, last_name, email, phone_number
                  FROM Volunteer
                  LEFT JOIN User
                  ON Volunteer.username = User.username
                  WHERE first_name LIKE '%$first_name_input%' 
                  AND last_name LIKE '%$last_name_input%' 
                  ORDER BY last_name ASC, first_name ASC";
        }
    }

        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "Failed to find Volunteers ... <br>" . __FILE__ ." line:". __LINE__ );
        }


//    if ( !is_bool($result) && (mysqli_num_rows($result) > 0) ) {
//        $row =  mysqli_fetch_array($result, MYSQLI_ASSOC);
//        $showup = TRUE;
//    } else {
//        array_push($error_msg,  "Query ERROR: No adopter found...<br>" . __FILE__ ." line:". __LINE__ );
//    }
}

 
?>
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
VOLUNTEER LOOKUP</h2></center>
<br>

<div class="container">
   
    <div class="center_content">
        <div class="center_left">
            <div class="adopter">
                <div class="profile_section">
                    <div class="subtitle" style="color:white;">Volunteer Lookup</div>
                    <form name="search_form" action="volunteer_lookup.php?go" method="POST">
                        <table>
                            <tr>

                                <td class="item_label addanitab">Enter first name</td>
                                <td><input type="text" name="first_name_input" value="<?php echo $first_name_input; ?>" /></td>
                                <span class="error_msg">* <?php echo $name_input_err_first;?></span>

                                <td class="item_label addanitab">Enter last name</td>
                                <td><input type="text" name="last_name_input" value="<?php echo $last_name_input; ?>" /></td>

                            </tr>

                        </table>
                        <a href="javascript:search_form.submit();" class="btn btn-primary fancy_button">Search</a>
                    </form>
                </div>

                <div class='profile_section'>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($result) && (mysqli_num_rows($result) > 0)) {

                        print "<div class='subtitle' style='color:white;'>Volunteers</div>";
                         print "<table class='table table-bordered tablemain' style='background-color:rgba(255, 255, 255, 0.9);'>";
						print "<tr><th>First Name</th><th>Last Name</th><th>Email</th><th>Phone Number</th></tr>";

                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                        {
                           print "<tr><td>" . $row['first_name'] . "</td><td>" . $row['last_name'] . "</td><td>" . $row['email'] . "</td><td>" . $row['phone_number'] . "</td></tr>";

                        }
						 print "</table>";
                            print "<br>";
						}
                    elseif (isset($result) && (mysqli_num_rows($result) == 0)) {
                        print "<div class='subtitle'>Sorry, no volunteers found</div>";
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
