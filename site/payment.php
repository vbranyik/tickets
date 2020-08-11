<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Payment screen
// --> Last Edit    : 11/17/14
// --> Upgrade ready:

session_start();

// --> Set paths
require_once dirname(__DIR__) . "/settings.php";

// --> Required libraries
require_once $sys_path['code'] . "/lib-customer.php";
require_once $sys_path['code'] . "/lib-invoice.php";
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
    check_auth(0);
}

// --> Check if user is cancelling. Redirect to promper page
if ( isset($_POST['btnCancel']) ) 
    { header("Location: ".$_SESSION['lastpageticket']); exit; }

// Set error code to zero
$error_msg = "";

// --> Set defaults for display
$display_type = "DEFAULT";
$forminfo = array();
$pagelabel = "Post Payment";

// --> Check to see if user is modifying a record
if ( isset($_GET['invno']) ) {
    $invno = $_GET['invno']; 
}

// --> User is posting payment
if ( isset($_POST['btnPost']) ) {
    $invno = $_POST['invno']; 
}

// --> Void Invoice
if ( isset($_POST['btnVoid']) ) {
    // --> Get Incident number
    $incno = $_POST['incno'];
    
    // --> Run function to void
    void_invoice($incno);
    
    // --> Exit back to calling page
    header("Location: ".$_SESSION['lastpageticket']); exit;
}

// --> Get invoice information
$invinfo = get_invoice($invno)->fetch(PDO::FETCH_ASSOC);

// --> Get customer information
$custinfo = get_customer($invinfo['custid'])->fetch(PDO::FETCH_ASSOC);

// --> Check to see if user is making a payment
if ( isset($_POST['btnPost']) ) {
    // --> Check to see if this will pay off the invoice
    if ( $invinfo['invtotal']-$invinfo['invbal']-$_POST['payment'] <= 0 ) { $paid = "YES"; }
    
        post_payment($invinfo['incno'], $invno,$_POST['txt_year_pmt']."-".$_POST['txt_month_pmt']."-".$_POST['txt_day_pmt'], $_POST['payment'],$_POST['notes'], $paid);
    
    // --> Exit back to calling page
    header("Location: ".$_SESSION['lastpageticket']); exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="Hired Gun Coding" />
    
    <!-- Style sheets -->
    <link rel="stylesheet" href="/styles/shared.css" />
	
    <title>Branyik Consulting | Ticket System | Login</title>
</head>
<body>
<form name="POSTPYMT" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" name="incno" value="<?php echo $invinfo['incno']; ?>" />
<input type="hidden" name="invno" value="<?php echo $invinfo['invno']; ?>" />
    <p>&nbsp;</p>
    <table class="main-create">
    <tr>
        <td class="centered">
        <table class="clear">
        <tr>
            <td class="header">Incident System</td>
        </tr>
        <tr>
            <td class="header-page-label"><? print $pagelabel; ?></td>
        </tr>
        <?PHP
        switch ($display_type)
        {
            default:
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Invoice Number";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $invinfo['invno'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Incident Number";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $invinfo['incno'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Company Name";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $custinfo['cname'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Invoice Total";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $invinfo['invtotal'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Invoice Balance";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $invinfo['invbal'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Payment Date";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        enter_date(time(),"_pmt");
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Payment amount";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<input class='filled-95' type='text' name='payment' value='".$invinfo['invtotal']."' style='text-align: center;' />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Notes";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<textarea class='filled-95' rows='5' name='notes'></textarea>";
                    print "</td>";
                print "</tr>";
                
                print "<tr><td>&nbsp;</td></tr>";
                
                // --> Nav buttons
                
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-3-left'>";
                            print "<input class='btn-gr' type='submit' name='btnPost' value='Post' />";
                        print "</td>";
                        print "<td class='submit-3-center'>";
                            print "<input class='btn-rd' type='submit' name='btnCancel' value='Cancel' />";
                        print "</td>";
                        print "<td class='submit-3-right'>";
                            print "<input class='btn-bl' type='submit' name='btnVoid' value='Void' />";
                        print "</td>";
                    print "</tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='centered'>";
                        
                    print "</td>";
                print "</tr>";
                
                break;
        }
        
        ?>
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