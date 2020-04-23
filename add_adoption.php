<?php
include('lib/common.php');

if((!isset($_SESSION['employee_type'])) || (!in_array($_SESSION['employee_type'], array('manager', 'employee')))) {
    header("Location: unauthorized_redirect.php"); /* Redirect browser */
    /* Make sure that code below does not get executed when we redirect. */
    exit;
}

$pet_id = htmlspecialchars($_GET['pet_id']);
$app_id = htmlspecialchars($_GET['app_id']);

$adoption_fee_err = $adoption_fee_err = $adoption_fee = $adoption_date = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $query_ready = TRUE;
    if(!isset($_POST['adoption_fee']) || empty($_POST['adoption_fee'] || $_POST['adoption_fee'] < 0 )){
        $query_ready = FALSE;
        $adoption_fee_err = "please enter adoption fee / correct amount";
     }
    else {
        $adoption_fee = mysqli_real_escape_string($db, $_POST['adoption_fee']);
     };

    if(!isset($_POST['adoption_date'])){
        $query_ready = FALSE;
        $adoption_date_err = "please select adoption date";
    }
    else {
        $adoption_date = mysqli_real_escape_string($db, $_POST['adoption_date']);
    };



    if ($query_ready) {

        $query = "INSERT INTO adoption(adoption_date, adoption_fee, applicant_number, pet_id)
                    VALUES('$adoption_date', '$adoption_fee', '$app_id', '$pet_id')";
        $result = mysqli_query($db, $query);

        include ('lib/show_queries.php');
        $inserted = FALSE;
        if (mysqli_affected_rows($db) == -1) {
            array_push($error_msg, "adoption insertion failed ... <br>" . __FILE__ ." line:". __LINE__ );

        }
        else {
            $inserted = TRUE;
        }
    }
}

//include("lib/header.php"); 
?>
<title>Add Adoption</title>

<link rel="stylesheet" href="css/bootstrap.css">
<script src="js/jquery.js"></script>
<script src="js/bootstrap.js"></script>
 <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
 <body style=" background-image:url(img/2.jpg); background-attachment:fixed;">
 <?php include("lib/menu.php"); ?>
 <div class="main"><br><br><br>
 <center><h2 style="font-weight:bold; color:#FFFFFF; text-shadow: 2px 2px 5px red;">
ADD NEW ADOPTION</h2></center>
<br>
 <br><br>
<div id="main_container">
<!--    --><?php //include("lib/menu.php"); ?>
    <div class="center_content">
       
        <form name="adoption_form" action="add_adoption.php?pet_id=<?php echo $pet_id ?>&app_id=<?php echo $app_id?>" method="POST">
            <table style="background-color:rgba(255, 255, 255, 0.9);">
                <tr>
                    <td class="item_label addanitab">Adoption fee</td>
                    <td><input type="number" name="adoption_fee" min="0.00" step="0.01"  value="<?php echo $adoption_fee ?>"/>
                        <span class="err_message">* <?php echo $adoption_fee_err;?></span></td>
                </tr>
                <tr>
                    <td class="item_label addanitab">Adoption date</td>
                    <td><input type="date" id="adoption_date" name="adoption_date" max = "<?php echo date('Y-m-d');?>" value="<?php echo $adoption_date ?>"/>
                        <span class="err_message">* <?php echo $adoption_date_err;?></span>
                    </td>
                </tr>

            </table><br>
            <a href="javascript:adoption_form.submit();" class="btn btn-primary btn-lg fancy_button">Submit</a>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $inserted ) {
            print "<div class='subtitle'>Adoption added successfully!</div>";
            header("refresh:5; url=animal_dashboard.php");
        }




        ?>
    </div>


    </div>
</div>


</body>
</html>






