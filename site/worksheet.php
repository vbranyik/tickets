<?php
session_start();

// --> Load paths based on server and then load libraries 
require_once dirname(__DIR__) . "/settings.php";

// --> Required libraries for FPDF
define('FPDF_FONTPATH', $sys_path['fonts']);
require_once $sys_path['pdf_path'] . "/fpdf.php";
require_once $sys_path['classes'] . "/class.phpmailer.php";


// --> Required libraries
require_once $sys_path['code'] . "/lib-browse.php";
require_once $sys_path['code'] . "/lib-customer.php";
require_once $sys_path['code'] . "/lib-incident.php";
require_once $sys_path['code'] . "/lib-invoice.php";
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";
require_once $sys_path['code'] . "/lib-worklog.php";

// --> Check to see if user is exiting page
if ( isset($_POST['btnCancel']) ) { header("Location: ".$_SESSION['lastpageticket']); exit; }

// Check to see if page authentication is needed
if (strtolower($auth) == "yes") {
  check_auth(0);
}

// --> Require to attach to database
// require("/home4/vbranyik/dbinfo/tickets.php");

// --> Set error message to blank
$error_message = "";

// --> Get incident number
if ( isset($_GET['incno']) ) { $_SESSION['incno'] = $_GET['incno']; }

// --> User is closing the incident
if ( isset($_POST['btnClose']) ) { close_incident($_SESSION['incno'],1); }

// --> Display list default
$page_size = 10;
$page_name = $_SERVER['PHP_SELF'];

// --> Work log insert defaults
$worklog = array();
$worklog['workdate'] = time();
$worklog['timein'] = time();
$worklog['timeout'] = time();
$worklog['units'] = "";
$worklog['rate'] = "";
$worklog['workperf'] = "";

// --> Submit block defaults
$submit_block = "WORKLOG";

// --> Get page start number or set to 0 if none passed
$startno = 0;   // --> Set a default
if ( isset($_GET['start']) ) { $startno = $_GET['start']; }
if ( isset($_POST['start']) ) { $startno = $_POST['start']; }

// --> If user is inserting a record
if ( isset($_POST['btnAdd']) ) {
    mod_worklog($_POST,"ADD");
} 

// --> Check to see if user is updating summary
if ( isset($_POST['btnSummary']) ) { $submit_block = "SUMMARY"; }

// --> User is updateing the incident summary
if ( isset($_POST['btnSumUpdate']) ) { update_summary($_SESSION['incno'],$_POST['summary']); }

// --> Get Incident information
$incinfo = get_incident($_SESSION['incno'])->fetch(PDO::FETCH_ASSOC);

// --> Get the customer information
$custinfo = get_customer($incinfo['custid'])->fetch(PDO::FETCH_ASSOC);

// --> User is creating the invoice
if ( isset($_POST['btnInvoice']) ) {
    // --> Check to see if can be invoiced
    $invoice_status = check_invoice($_SESSION['incno']);
    
    switch ( $invoice_status ) {
        case "NONE":
            // --> OK to invoice

            // --> Get invoice total
            $invtotal = gen_invoice($_SESSION['incno'],"total")->fetch(PDO::FETCH_ASSOC);

            // --> Insert invoice detail
            $invno = insert_invoice($_SESSION['incno'],$incinfo['custid'],$invtotal['grand_total']);

            // --> Update incident with invoice detail
            add_invoice_info($_SESSION['incno'],$invno,2);
            
            // --> Create the PDF file
            print_invoice($_SESSION['incno'],"F");
            
            // --> Set invoice number and finished status
            $incinfo['invno'] = $invno;
            $incinfo['finished'] = 2;
            
            // --> Email the invoice
            $error_message = send_invoice($invno,$_SESSION['incno']);

            break;
        case "NOTCLOSED":
            // --> Set error message
            $error_message = "The incident has not been closed.";
            break;
        case "INVOICED":
            // --> Set error message
            $error_message = "The incident has already been invoiced.";
            break;
    }
    
}

// --> User is resending an invoice
if ( isset($_POST['btnResend']) ) { 
    $error_message = send_invoice($incinfo['invno'],$_SESSION['incno']);  
}



// --> Get total for work done
$invinfo = gen_invoice($_SESSION['incno'],"total")->fetch(PDO::FETCH_ASSOC);
// --> $invinfo = active_list_find($_SESSION['incno'],"CUSTOM02",$page_name,$startno,$page_size)->fetch(PDO::FETCH_ASSOC);

// -->  Get Work Log records
$log_result = active_list_find($_SESSION['incno'],"CUSTOM01",$page_name,$startno,$page_size);

?>

<!DOCTYPE html>
<html>
    <head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<meta name="author" content="Hired Gun Coding" />
    
        <!-- Style sheets -->
        <link rel="stylesheet" href="/styles/shared.css" />
        <link rel="stylesheet" href="/styles/worksheet.css" />
	
    <title>Branyik Consulting | Ticket System | Login</title>
    </head>
    <body>
        <p>&nbsp;</p>
        <table class="main">
        <tr>
            <td class="centered">
            <table class="clear">
            <tr>
                <td class="header">Incident System</td>
            </tr>
            <tr>
                <td class="centered">
                <table class="clear">
                <?PHP
                // --> Set defaults for buttons
                $closed_disable = "";
                $invoice_disable = "";
                
                // --> Determine Invoice status
                print "<form name='INCLOG'action='".$_SERVER['PHP_SELF']."' method='POST'>";
                switch ( $incinfo['finished'] )
                {
                        case 3:
                            $incstatus = "Complete, Paid";
                            $closed_disable = " disabled";
                            $invoice_disable = " disabled";
                            $incident_disable = " disabled";
                            break;
                        
                        case 2:
                            $incstatus = "Complete, Unpaid";
                            $closed_disable = " disabled";
                            $invoice_disable = " disabled";
                            $incident_disable = " disabled";
                            break;
                        
                        case 1:
                            $incstatus = "Complete, Not Billed";
                            $closed_disable = " disabled";
                            $incident_disable = " disabled";
                            break;
                        
                        default:
                            $incstatus = "Open";
                            $invoice_disable = " disabled";
                }

                // --> Display Incident details
                print "<tr valign='top'>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='inc-info-1'><b>Inc #:</b>&nbsp;".$_SESSION['incno']."</td>";
                        print "<td class='inc-info-2'><b>Date:</b>&nbsp;".date("m/d/Y",strtotime($incinfo['incdate']))."</td>";
                        print "<td class='inc-info-3'><b>Invoice #:</b>&nbsp;".$incinfo['invno']."</td>";
                        print "<td class='inc-info-4'><b>Total:</b>&nbsp;".number_format($invinfo['grand_total'],2,".",",")."</td>";
                        print "<td class='inc-info-5'><b>Status:</b>&nbsp;".$incstatus."</td>";
                    print "</tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='left'><b>Customer:</b>&nbsp;".htmlspecialchars($custinfo['cname'])."</td>";
                print "</tr>";
                print "<tr valign='top'>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr valign='top'>";
                        print "<td class='col-50-left'><b>Equipment to service:</b>&nbsp;".htmlspecialchars($incinfo['equipment'])."</td>";
                        print "<td class='col-50-left'><b>Issue:</b>&nbsp;".htmlspecialchars($incinfo['issue'])."</td>";
                    print "</tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='left'><b>Summary:</b>&nbsp;".htmlspecialchars($incinfo['summary'])."</td>";
                print "</tr>";
                
                // --> Submit line
                
                print "<tr><td class='centered'><hr /></td></tr>";

                // --> Check to see if there is a message to display
                if ( trim($error_message) != "" ) {
                    print "<tr><td class='error-dull'>".$error_message."</td></tr>";
                }
                
                print "<tr valign='top'>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-6-1'>";
                            print "<input type='submit' name='btnResend' value='Resend' class='btn-bk' />";
                        print "</td>";
                        print "<td class='submit-6-2'>";
                            print "<input type='submit' name='btnInvoice' value='Invoice' class='btn-bk'".$invoice_disable." />";
                        print "</td>";
                        print "<td class='submit-6-3'>";
                            print "<input type='submit' name='btnIncident' value='Incident' class='btn-bk'".$incident_disable." />";
                        print "</td>";
                        print "<td class='submit-6-4'>";
                            print "<input type='submit' name='btnClose' value='Close' class='btn-bk'".$closed_disable." />";
                        print "</td>";
                        print "<td class='submit-6-5'>";
                            print "<input type='submit' name='btnSummary' value='Summary' class='btn-bk' />";
                        print "</td>";
                        print "<td class='submit-6-6'>";
                            print "<input type='submit' name='btnCancel' value='Exit' class='btn-rd' />";
                        print "</td>";
                    print "</td>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                    
                
                // -->  Add data entry line in the event the Incident is open
                switch ($submit_block)
                {
                    case "SUMMARY":
                        print "<tr><td class='centered'><hr /></td></tr>";
                        print "<tr><td class='centered'>Edit Summary</td></tr>";
                        print "<tr>";
                            print "<td class='centered'>";
                            print "<table class='clear'>";
                            print "<tr>";
                                print "<td class='work-entry-work'><textarea class='filled-98' name='summary' rows='1'>".htmlspecialchars($incinfo['summary'])."</textarea></td>";
                                print "<td class='work-entry-submit' valign='middle'>";
                                print "<input type='submit' name='btnSumUpdate' value='Update' /><br /><input type='submit' name='btnSumExit' value='Cancel' class='btn-dyn-rd' />";
                                print "</td>";
                            print "</tr>";
                            print "</table>";
                            print "</td>";
                        print "</tr>";

                        break;
                    
                    default:
                    if ( $incinfo['finished'] == 0 ) {
                    print "<input type='hidden' name='incno' value='".$_SESSION['incno']."' />";
                        print "<tr><td class='centered'><hr /></td></tr>";
                        print "<tr>";
                            print "<td class='centered'>";
                            print "<table class='clear'>";
                            print "<tr valign='top'>";
                                print "<td class='work-entry-date'><b>Date&nbsp;</b>";
                                    enter_date($worklog['workdate'],"_log");
                                print "</td>";
                                print "<td class='work-entry-timein'><b>IN&nbsp;</b>";
                                    enter_time($worklog['timein'],"_in");
                                print "</td>";
                                print "<td class='work-entry-timeout'><b>OUT&nbsp;</b>";
                                    enter_time($worklog['timeout'],"_out");
                                print "</td>";
                                print "<td class='work-entry-units-label'>#&nbsp;</td>";
                                print "<td class='work-entry-units'><input type=\"text\" name=\"units\" value=\"".$worklog['units']."\" class=\"filled-98\" /></td>";
                                print "<td class='work-entry-rate-label'>Rate&nbsp;</td>";
                                print "<td class='work-entry-rate'><input type=\"text\" name=\"rate\" value=\"".$worklog['rate']."\" class=\"filled-98\" /></td>";
                            print "</tr>";
                            print "</table>";
                            print "</td>";
                        print "</tr>";

                        print "<tr>";
                            print "<td class='centered'>";
                            print "<table class='clear'>";
                            print "<tr>";
                                print "<td class='work-entry-work'><textarea class='filled-98' name='workperf' rows='1'></textarea></td>";
                                print "<td class='work-entry-submit' valign='middle'><input type='submit' name='btnAdd' value='Add'></td>";
                            print "</tr>";
                            print "</table>";
                            print "</td>";
                        print "</tr>";
                    }
                }
                
                // --> Display Work Log
                print "<tr>";
                    print "<td class='centered'>";
                        print "<table class='clear'>";
                        print "<tr valign='top'>";
                            print "<td class='browse-log-header-edit'>&nbsp;</td>";
                            print "<td class='browse-log-header-del'>&nbsp;</td>";
                            print "<td class='browse-log-header-1'>Date</td>";
                            print "<td class='browse-log-header-2'>Work</td>";
                            print "<td class='browse-log-header-3'>In</td>";
                            print "<td class='browse-log-header-4'>Out</td>";
                            print "<td class='browse-log-header-5'>Units</td>";
                            print "<td class='browse-log-header-6'>Rate</td>";
                            print "<td class='browse-log-header-7'>Total</td>";
                        print "</tr>";


                        // --> Counter for page
                        $z = 0;

                        while ($log_info = $log_result->fetch(PDO::FETCH_ASSOC))
                        {
                            // --> Format times
                            if ( $log_info['timein'] == "00:00:00" ) {
                                $log_info['timein'] = ""; 
                            }
                            else {
                                $log_info['timein'] = date("h:iA",strtotime($log_info['timein']));
                            }

                            if ( $log_info['timeout'] == "00:00:00" ) {
                                $log_info['timeout'] = ""; 
                            }
                            else {
                                $log_info['timeout'] = date("h:iA",strtotime($log_info['timeout']));
                            }

                            print "<tr valign='top'>";
                                print "<td class='browse-log-edit'>";
                                    print "&nbsp;";
                                print "</td>";
                                print "<td class='browse-log-del'>";
                                    print "&nbsp;";
                                print "</td>";
                                print "<td class='browse-log-1'>";
                                    print date("m/d/y",strtotime($log_info['workdate']));
                                print "</td>";
                                print "<td class='browse-log-2'>";
                                    print htmlspecialchars($log_info['workperf']);
                                print "</td>";
                                print "<td class='browse-log-3'>";
                                    print $log_info['timein'];
                                print "</td>";
                                print "<td class='browse-log-4'>";
                                    print $log_info['timeout'];
                                print "</td>";
                                print "<td class='browse-log-5'>";
                                    print $log_info['units'];
                                print "</td>";
                                print "<td class='browse-log-5'>";
                                    print $log_info['rate'];
                                print "</td>";
                                print "<td class='browse-log-5'>";
                                    print number_format($log_info['line_total'],2);
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
                    print "</table>";
                    print "</td>";
                print "</tr>";
                print "</form>";    
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
                <td class="centered"></td>
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
