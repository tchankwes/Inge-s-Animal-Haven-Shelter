<div id="footer"> 
  <div class="footer">  
    <div class="resource_title">
    <?php if(isset($_SESSION['employee_type'])) {
        echo 'Logged in as ' . $_SESSION['first_name']. ' ' . $_SESSION['last_name'] . ' (' . $_SESSION['employee_type'] . ')';
      } else {
        echo 'Public view (employees, log in above)';
      } ?>
    </div>
  </div>
</div>	 

