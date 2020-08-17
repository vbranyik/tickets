<?php
session_start();

$_SESSION['returnURL'] = "/tickets";

// --> Load paths based on server and then load libraries 
require_once dirname(__DIR__) . "/settings.php";

// --> Required libraries
require_once $sys_path['code'] . "/lib-browse.php";
require_once $sys_path['code'] . "/lib-required.php";

// Check to see if logged in, ignore if on dev server
if (strtolower($auth) !="no") {
    check_auth(0);
}

// --> Check to see if user is selecting a button at the bottom of the page
if ( isset($_POST['btnAutoBill']) ) { header("location: /setauto"); exit; }
if ( isset($_POST['btnIncident']) ) { header("location: /incident"); exit; }
if ( isset($_POST['btnTimeEntry']) ) { header("location: /time"); exit; }

// --> Page size default
$page_size = 15;
$page_name = $_SERVER['PHP_SELF'];

// --> Get page start number or set to 0 if none passed
$startno = 0;   // --> Set a default
if ( isset($_GET['start']) ) { $startno = $_GET['start']; }
if ( isset($_POST['start']) ) { $startno = $_POST['start']; }

// --> Set page for return
$_SESSION['lastpageticket'] = "/tickets/".$startno;
$_SESSION['lastpagetickettime'] = time();

// -->  Get records
if ( isset($_POST['btnUnpaid']) ) {
    $inc_result = active_list_find("incidents","CUSTOM03",$page_name,$startno,30);
} else {
  if (isset($_POST['btnOpen'])) {
    $listcfg = "CUSTOM04";
  }
  else {
    $listcfg = "CUSTOM00";
  }
  $inc_result = active_list_find("incidents", $listcfg, $page_name, $startno, $page_size);
}
    
?>

<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="Hired Gun Coding" />

    <!-- Style sheets -->
    <link rel="stylesheet" href="/styles/shared.css" />

    <title>Branyik Consulting | Ticket System | Login</title>
</head>
<body>
  <p>&nbsp;</p>
  <form nsme="TicketList" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
  <table class="main">
  <tr>
    <td class="centered">
    <table class="clear">
    <tr>
        <td class="header">Incident System</td>
    </tr>
    <tr>
      <td class="control-row">
        Beginning Year <?php print firstYearSelect(); ?>
      </td>
    </tr>
    <tr>
      <td class="centered">
      <table class="clear">
      <?PHP
      print "<tr valign='top'>";
        print "<td class='browse-inc-header-edit'>&nbsp;</td>";
        print "<td class='browse-inc-header-edit'>&nbsp;</td>";
        print "<td class='browse-inc-header-pay'>&nbsp;</td>";
        print "<td class='browse-inc-header-1'>#</td>";
        print "<td class='browse-inc-header-2'>Date</td>";
        print "<td class='browse-inc-header-3'>Customer Name</td>";
        print "<td class='browse-inc-header-4'>Problem</td>";
        print "<td class='browse-inc-header-5'>Status</td>";
      print "</tr>";

      // --> Counter for page
      $z = 0;

      while ($inc_info = $inc_result->fetch(PDO::FETCH_ASSOC))
      {
        // --> Determine status
        switch ( $inc_info['finished'] )
        {
          case 4:
            $finished = "Complete, Voided";
            break;

          case 3:
            $finished = "Complete, Paid";
            break;

          case 2:
            $finished = "Billed, $".$inc_info['invtotal'];
            break;

          case 1:
            $finished = "Complete, Not Billed";
            break;

          default:
            $finished = "In Process";
        }

          print "<tr valign='top'>";
            print "<td class='browse-inc-edit'>";
              print "<a target='_self' href='/worksheet/edit/".$inc_info['incno']."'><img src='/images/wks.png' /></a>"; 
            print "</td>";
            print "<td class='browse-inc-edit'>";
              if ( $inc_info['finished'] == 0 ) {
              print "<a target='_self' href='/incident/edit/".$inc_info['incno']."'><img height='12' width='12' src='/images/edit.png' /></a>"; 
              }
              else {
                print "<img height='12' width='12' src='/images/edit-bw.png' />";
              }
            print "</td>";
            print "<td class='browse-inc-pay'>";
              switch ($inc_info['finished'])
              {
                case 4:
                  print "<img src='/images/dollar_void.png' />";
                  break;
                case 3:
                  print "<img src='/images/dollar_paid.png' />";
                  break;
                case 2:
                 print "<a target='_self' href='/payment/".$inc_info['invno']."'><img src='/images/dollar_billed.png' /></a>";
                  break;
                case 1:
                  print "<img src='/images/dollar_no_bill.png' />";
                  break;
                case 0:
                  print "<img src='/images/dollar_in_process.png' />";
              }
            print "</td>";
            print "<td class='browse-inc-1'>";
              print $inc_info['incno'];
            print "</td>";
            print "<td class='browse-inc-2'>";
              print date("m/d/y",strtotime($inc_info['incdate']));
            print "</td>";
            print "<td class='browse-inc-3'>";
              print $inc_info['cname'];
            print "</td>";
            print "<td class='browse-inc-4'>";
              print $inc_info['issue'];
            print "</td>";
            print "<td class='browse-inc-5'>";
              print $finished;
            print "</td>";
          print "</tr>";

          // --> Increment counter
          $z++;
      }

      // --> Fill rest of page if needed
      if ( $z < $page_size ) { 
        for ($z; $z<=$page_size; $z++)
        { print "<tr><td colspan='5'>&nbsp;</td></tr>"; }
      }
      ?>
      </table>
      </td>
    </tr>
    <tr>
      <td class="centered"><hr /></td>
    </tr>
    <?PHP
      active_list_control($startno,$page_size,$page_name)
    ?>
    <tr>
      <td class="centered">&nbsp;</td>
    </tr>
    <tr>
      <td class="centered">
      <table class="clear">
      <tr>
        <td class="submit-6-left">
          <input class="btn-gr" type="submit" name="btnTimeEntry" value="Time Entry" />
        </td>
        <td class="submit-6-mid">
          <input class="btn-bk" type="submit" name="btnOpen" value="Open Incidents" />
        </td>
        <td class="submit-6-mid">
          <input class="btn-gr" type="submit" name="btnIncident" value="New Incident" />
        </td>
        <td class="submit-6-mid">
          <input class="btn-bl" type="submit" name="btnAutoBill" value="Auto Bill" />
        </td>
        <td class="submit-6-mid">
          <input class="btn-bl" type="submit" name="btnUnpaid" value="Unpaid Invoices" />
        </td>
        <td class="submit-6-right">
          <input class="btn-bk" type="submit" name="btnReset" value="Refresh" />
        </td>
      </tr>
      </table>
      </td>
    </tr>
    <tr>
      <td class="centered">&nbsp;</td>
    </tr>
    </table>
    </td>
  </tr>
  </table>
  </form>
  </body>
</html>
