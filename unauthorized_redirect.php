<?php
include('lib/common.php');
header(REFRESH_TIME . 'url=index.php');
?>

<?php include("lib/header.php"); ?>
<title>Unauthorized Access</title>
</head>
<body>
  <div id="main_container">
    <?php include("lib/menu.php"); ?>
    <div class="center_content">
      <div class="title">Not authorized to view this content</div>
      <a>Redirecting to login page</a>
      <?php include("lib/error.php"); ?>
      <div class="clear"></div>
    </div>
    <?php include("lib/footer.php"); ?>
  </div>
</body>
</html>