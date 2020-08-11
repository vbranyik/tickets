<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Year End Processing
// --> Last Edit    : 01/26/20
// --> Upgrade ready:

// --> Is print selected
if (isset($_POST['print'])) {
  header("Location: /printyear/" . $_POST['print']);
}


session_start();

// --> Load paths based on server and then load libraries 
require "code/system.php";
$sys_path = set_paths();

// --> Required libraries
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";
require_once $sys_path['code'] . "/lib-yearend.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
  check_auth(0);
}

// --> Get the paymets made
if (isset($_POST['startYear'])) {
  $yearEnd = $_POST['startYear'];
}
else {
  $yearEnd = date("Y");
}
$pymts = getPayments($yearEnd);

// --> Set error message to none
$err_msg = "none";
$reg_msg = "none";

// Set error code to zero
$error_msg = "";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <meta name="author" content="Hired Gun Coding" />

  <!-- Style sheets -->
  <link rel="stylesheet" href="/styles/shared.css" />
  <link rel="stylesheet" href="/styles/setauto.css" />

  <title>Branyik Consulting | Ticket System | Login</title>
</head>
<body>
  <p>&nbsp;</p>
  <table class="clear-50">
  <tr>
    <td class="centered">
    <table class="clear">
    <tr>
      <td class="centered"><h1>Incident System</h1></td>
    </tr>
    <tr>
      <td class="centered"><h2>Year End Payments Report <?php print $yearEnd; ?></h2></td>
    </tr>
    <tr>
      <td class="centered">
      <table id="list" class="clear separate">
      <?php
        // --> Display records
        $total = 0;
        
        for ($z=0; $z<count($pymts); $z++) {
          print "<tr>\n";
          print "  <td class='td.left-33'>" . date("m/d/Y",strtotime($pymts[$z]['pdate'])) . "</td>";
          print "  <td class='td.left-33'>" . $pymts[$z]['customer'] . "</td>";
          print "  <td class='td.right-33'>" . $pymts[$z]['amount'] . "</td>";
          print "</tr>\n";
          
          $total += $pymts[$z]['amount'];
        }
      ?>
        <tr>
          <td class='td.left-33'>&nbsp;</td> 
          <td class='td.left-33'>&nbsp;</td> 
          <td class='td.right-33'>&nbsp;</td> 
        </tr>  
        <tr>
          <td class='td.left-33'>Total</td> 
          <td class='td.left-33'>&nbsp;</td> 
          <td class='td.right-33'><?php print number_format($total, 2); ?></td> 
        </tr>  
      </table>
      </td>
    </tr>
    <tr>
      <td class="centered"></td>
    </tr>
    <tr>
      <td class="centered">
        <form action="/yearend" method="POST">
          Select Year <?php print firstYearSelect($yearEnd, "payments"); ?><br /><br />
          Print Report<input type="checkbox" id="print" name="print" value="<?php print $yearEnd; ?>"/><br /><br />
          <input type="submit" value="Submit">
        </form>
      </td>
    </tr>
    </table>
    </td>
  </tr>
  </table>
</body>
<script type="text/javascript">
function setYear() {
  var yearToSet = document.getElementById('year').value;
  document.getElementById('print').value = yearToSet;
  console.log(yearToSet);
}
</script>
</html>