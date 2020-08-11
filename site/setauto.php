<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Setup Autobill
// --> Last Edit    : 12/28/17
// --> Upgrade ready:

session_start();

$display_type =  "DEFAULT";

// --> Load paths based on server and then load libraries 
require "code/system.php";
$sys_path = set_paths();

// --> Required libraries
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-setauto.php";
require_once $sys_path['code'] . "/lib-time.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
  check_auth(0);
}

// --> Send invoices
if (isset($_POST['SendAuto'])) { header("location: /autobill.php"); exit; }

// --> Exit back to tickets
if (isset($_POST['CancelExit'])) { header("location: /tickets"); exit; }

// --> Set error message to none
$err_msg = "none";
$reg_msg = "none";

// --> Set display type to default if no autoid has been passed
if ( isset($_POST['autoid']) || isset($_GET['autoid']) ) {
  if (  isset($_POST['autoid']) ) {
    $autoid = $_POST['autoid'];
  } else {
    $autoid = $_GET['autoid'];
  }
  $display_type = "EDIT";
} else {
  $display_type = "DEFAULT";
}

// --> Look for keys that are presses in this section
// --> If the save key has been pressed
if (isset($_POST['SaveRec'])) {
  $autoid = $_POST['autoid'];
  $checkid = save_record($_POST, "update");
    
  if ( $autoid != $checkid ) {
    // --> Set error message
    $err_msg = "There was an error saving the record<br />the record was not modified";
  } else {
    $reg_msg = "Your record was saved";
  }
    
    $display_type = "EDIT";
}

// --> The Cancel key was pressed
if ( isset($_POST['CancelEdit'])) {
  $display_type = "DEFAULT";
}

// Set error code to zero
$error_msg = "";

// --> Get record(s) depending on type of page
switch ( $display_type ) {
  case "EDIT":
    $record = get_record($autoid);
    break;
  default:
    // --> Get records
    $bills = get_autobill_recs();
}

if ($display_type == "DEFAULT") {
  $table_class = "main-wide";
} else {
  $table_class = "main";
}

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
  <table class="<?php echo $table_class; ?>">
  <tr>
    <td class="centered">
    <table class="clear">
    <tr>
      <td class="header">Incident System</td>
    </tr>
    <tr>
      <td class="centered">
      <table id="list" class="clear separate">
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" >
      <?php
      switch($display_type) {
        case "EDIT":
          $btn_exit = "CancelEdit";
          $btn_label = "Save";
          $btn_save = "SaveRec";
          $colspan = 2;
      ?>
      <input type="hidden" name="autoid" value="<?php echo $autoid; ?>" />
      <tr>
        <td class="label">Frequency (months):</td>
        <td class="input-right"><input type="text" name="freq" value="<?php echo $record['freq']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Customer Name:</td>
        <td class="input-right"><?php echo $record['cname']; ?></td>
      </tr>
      <tr>
        <td class="label">Requested by:</td>
        <td class="input-right"><input type="text" name="reqby" value="<?php echo $record['reqby']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Equipment:</td>
        <td class="input-right"><input type="text" name="equipment" value="<?php echo $record['equipment']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Issue:</td>
        <td class="input-right"><textarea name="issue"><?php echo $record['issue']; ?></textarea></td>
      </tr>
      <tr>
        <td class="label">Location:</td>
        <td class="input-right"><input type="text" maxlength="50" name="location" value="<?php echo $record['location']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Phone number:</td>
        <td class="input-right"><input type="text" name="phone" value="<?php echo $record['phone']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Work Performed:</td>
        <td class="input-right"><textarea name="workperf"><?php echo $record['workperf']; ?></textarea></td>
      </tr>
      <tr>
        <td class="label">Units:</td>
        <td class="input-right"><input type="text" name="units" value="<?php echo $record['units']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Rate:</td>
        <td class="input-right"><input type="text" name="rate" value="<?php echo $record['rate']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Next Billing Date:</td>
        <td class="input-right"><input type="text" name="nextbill" value="<?php echo $record['nextbill']; ?>" /></td>
      </tr>
      <tr>
        <td class="label">Expire Date:</td>
        <td class="input-right"><input type="text" name="expire" value="<?php echo $record['expire']; ?>" /></td>
      </tr>
      <tr><td colspan="<?php echo $colspan; ?>">&nbsp;</td></tr>    
      <tr><td colspan="<?php echo $colspan; ?>">&nbsp;</td></tr>    
      <?php if ( $err_msg != "none" ) { ?>
      <script>alert("There was an error saving the record!");</script>
      <tr>
        <td colspan="<?php echo $colspan; ?>" class="error"><?php echo $err_msg; ?></td>
      </tr>
      <tr><td colspan="<?php echo $colspan; ?>">&nbsp;</td></tr> 
      <?php } ?>
      <?php if ( $reg_msg != "none" ) { ?>
      <tr>
        <td colspan="<?php echo $colspan; ?>" class="status"><?php echo $reg_msg; ?></td>
      </tr>
      <tr><td colspan="<?php echo $colspan; ?>">&nbsp;</td></tr> 
      <?php } 
        break;
        default:
          $btn_exit = "CancelExit";
          $btn_label = "Send";
          $btn_save = "SendAuto";
          $colspan = 10;
      ?>
      <tr>
        <th>ID</th>
        <th>FRQ</th>
        <th>Terms</th>
        <th>Cust</th>
        <th>Issue</th>
        <th>Work</th>
        <th>Units</th>
        <th>Rate</th>
        <th>Next Bill</th>
        <th>Expire</th>
      </tr>
      <tr><td colspan="<?php echo $colspan; ?>"><hr /></td></tr>
      <?php foreach ($bills AS $b) { ?>
      <?php if($b['nextbill'] == date("Y-m-d")) { ?>
      <tr style='background-color: #fff; color: #00A321'>
      <?php } else { ?>
      <tr>
      <?php } ?>
        <td class="id">
          <a target="_self" href="<?PHP echo $_SERVER['PHP_SELF']."?autoid=".$b['autoid']; ?>">
            <?php echo $b['autoid']; ?>
          </a>
        </td>
        <td class="freq"><?php echo $b['freq']; ?></td>
        <td class="terms"><?php echo $b['terms']; ?></td>
        <td class="cname"><?php echo $b['cname']; ?></td>
        <td class="issue"><?php echo $b['issue']; ?></td>
        <td class="work"><?php echo $b['work']; ?></td>
        <td class="units"><?php echo $b['units']; ?></td>
        <td class="rate"><?php echo $b['rate']; ?></td>
        <td class="date"><?php echo date("m/d/y",strtotime($b['nextbill'])); ?></td>
        <td class="date"><?php echo date("m/d/y",strtotime($b['expire'])); ?></td>
      </tr>
      <?php } ?>
      <tr><td colspan="<?php echo $colspan; ?>"><hr /></td></tr>
      <?php } ?>    
      <tr>
        <td colspan="<?php echo $colspan; ?>" class="centered">
        <table class="clear">
        <tr>
          <td class="submit-2-left">
            <input class="btn-gr" type="submit" name="<?php echo $btn_save; ?>" value="<?php echo $btn_label; ?>" />
          </td>
          <td class="submit-2-right">
            <input class="btn-rd" type="submit" name="<?php echo $btn_exit; ?>" value="Exit" />
          </td>
        </tr>
        </table>
        </td>
      </tr>    
      </form>
      </table>
      </td>
    </tr>
    <tr>
      <td class="centered"></td>
    </tr>
    </table>
    </td>
  </tr>
  </table>
</body>
</html>