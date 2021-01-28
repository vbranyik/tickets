<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Login screen
// --> Last Edit    : 11/09/14
// --> Upgrade ready:

session_start();

// --> Load paths based on server and then load libraries 
//require "code/system.php";
//$sys_path = set_paths();

// --> Load paths based on server and then load libraries 
require_once dirname(__DIR__) . "/settings.php";

// --> Required libraries
require_once $sys_path['code'] . "/lib-customer.php";
require_once $sys_path['code'] . "/lib-incident.php";
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
    check_auth(0);
}

// --> Check if user is cancelling. Redirect to promper page
if ( isset($_POST['btnCancel']) ) {   // --> Determine which page to goto based on time
  if ( $_SESSION['lastworksheettime'] > $_SESSION['lasttickettime'] ) {
    header("Location: ".$_SESSION['lastpageworksheet']);
  }
  else {
    header("Location: ".$_SESSION['lastpageticket']);
  }
}

// --> Database connect profiles
// require("/home4/vbranyik/dbinfo/tickets.php");

// Set error code to zero
$error_msg = "";

// --> Set defaults for display
$display_type = "DEFAULT";
$forminfo = array();
$pagelabel = "Create Incident";

// --> Check to see if user is modifying a record
if ( isset($_GET['incno']) ) { $incno = $_GET['incno']; $display_type = "MODIFY"; }
if ( isset($_POST['incno']) ) { $incno = $_POST['incno']; $display_type = "MODIFY"; }

// --> User is creating an incident that needs to be confirmed
if ( isset($_POST['btnCreate']) ) { $display_type = "CREATE"; }
if ( isset($_POST['btnConfirm']) ) { $display_type = "CONFIRMED"; }

// --> If the user is saving a record
if ( isset($_POST['btnSave']) ) {
    mod_incident($_POST,"MOD");
}

// --> If this is the default screen
if ( $display_type == "DEFAULT" || $display_type == "MODIFY"  ) {
    // --> Get the customer list
    $cust_list = get_cust_list(0);
    
    // --> Set default values for the form
    $forminfo['incdate'] = time();
    $forminfo['custid'] = "";
    $forminfo['reqby'] = "";
    $forminfo['rate'] = "";
    $forminfo['equipment'] = "";
    $forminfo['location'] = "";
    $forminfo['phone'] = "";
    $forminfo['issue'] = "";
    $forminfo['recstart'] = time();
    $forminfo['recend'] = time();
    $forminfo['units'] = "";
    $forminfo['rate'] = "";
    $forminfo['freq'] = "";
    $forminfo['work'] = "";
}

// --> If the user need to confirm creating the incident
if ( $display_type == "CREATE" ) {
    // --> Transfer POST variable
    $forminfo = $_POST;


    // --> Convert date string to Time interger
    $forminfo['incdate'] = strtotime($_POST['txt_year_inc']."-".$_POST['txt_month_inc']."-".$_POST['txt_day_inc']);
    $forminfo['recstart'] = strtotime($_POST['txt_year_start']."-".$_POST['txt_month_start']."-".$_POST['txt_day_start']);
    $forminfo['recend'] = strtotime($_POST['txt_year_end']."-".$_POST['txt_month_end']."-".$_POST['txt_day_end']);
    
    // --> Get customer information
    $custinfo = get_customer($_POST['custid'])->fetch(PDO::FETCH_ASSOC);
    
    // --> Set page label
    $pagelabel = "Confirm New Incident";
}

// --> Create the record
if ( $display_type == "CONFIRMED" ) {
    $forminfo['incno'] = mod_incident($_POST,"ADD");
    
    // --> Check result and set display type accordingly
    if ( $forminfo['incno'] > 0 ) {
        // --> The incident was created
        $display_type = "COMPLETE";
        
        // --> Set page label
        $pagelabel = "New Incident";
    }
    else {
        // --> There was an error
        $display_type = "ERROR";
    }
}

// --> If user is modifying record
if ( $display_type == "MODIFY" ) {
    // --> Get incident information
    $forminfo = get_incident($incno)->fetch(PDO::FETCH_ASSOC);

    // --> Convert date string to Time interger
    $forminfo['incdate'] = strtotime($forminfo['incdate']);
    
    // --> Set page label
    $pagelabel = "Modify Incident";
    
    // --> Check to see if closed
    if ( $forminfo['finished'] == 1 ) { 
        // --> Set display type
        $display_type = "CLOSED";
    
        // --> Get customer information
        $custinfo = get_customer($forminfo['custid'])->fetch(PDO::FETCH_ASSOC);
        
        // --> Set page label
        $pagelabel = "View Incident";
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="Hired Gun Coding" />
    
    <!-- Style sheets -->
    <link rel="stylesheet" href="/styles/shared.css" />
    <link rel="stylesheet" href="/styles/create_incident.css" />
	
    <title>Branyik Consulting | Ticket System | Login</title>
    
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
</head>
<body>
<form name="CREATEINC" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
    <p>&nbsp;</p>
    <table class="main-create">
    <tr>
        <td class="centered">
        <table class="clear">
        <tr>
            <td class="header">Incident System</td>
        </tr>
        <tr>
            <td class="header-page-label"><?php print $pagelabel; ?></td>
        </tr>
        <?PHP
        // --> If this is a complete incident
        if ( $display_type == "CLOSED" ) {
            print "<tr><td class='title-red'>** A completed Incident cannot be modified **</td></tr>";
        }

        switch ($display_type) {
            case "COMPLETE";
                print "<tr><td class='whitespace-20'>&nbsp;</td></tr>";
                print "<tr>";
                    print "<td class='title-item'>Incident</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='header-inc-num-red'>".$forminfo['incno']."</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='title-item'>Created!</td>";
                print "</tr>";
                print "<tr><td class='whitespace-20'>&nbsp;</td></tr>";

                // --> Nav buttons
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-2-left'>";
                            print "<input class='btn-bk' type='submit' name='btnAgain' value='Create Another' />";
                        print "</td>";
                        print "<td class='submit-2-right'>";
                            print "<input class='btn-rd' type='submit' name='btnCancel' value='Cancel' />";
                        print "</td>";
                    print "</tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                
                
                break;
            
            case "CREATE":
            case "CLOSED":
                if ( $display_type == "CREATE" ) {
                    // --> Put POST variable into hidden variables to transfer to next page
                    foreach ($_POST as $key => $value) {
                        print "<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\"/>"; 
                    }
                }
                
                // --> Display incident date if the 'etype' = std (standard)
                if ($forminfo['etype']=="std") {
                    print "<tr>";
                        print "<td class='input-label-above'>";
                            print "Incident Date";
                        print "</td>";
                    print "</tr>";
                    print "<tr>";
                        print "<td class='centered'>";
                            print date("m/d/Y",$forminfo['incdate']);
                        print "</td>";
                    print "</tr>"; 
                }
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
                        print "Requested By";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $forminfo['reqby'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Rate";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $forminfo['hourlyrate'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Equipment To Service";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $forminfo['equipment'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Equipment Location";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $forminfo['location'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Contact Phone";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print $forminfo['phone'];
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Issue";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='left-pad-25'>";
                        print $forminfo['issue'];
                    print "</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='centered'>";
                    print "</td>";
                print "</tr>";

                // --> Display recurring nformation if the 'etype' = rec (recurring)
                if ($forminfo['etype']=="rec") {
                    print "<tr>";
                        print "<td class='header-page-label'>Recurring Entries</td>";
                    print "</tr>";
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                            print "Start Date";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                            print date("m/d/Y",$forminfo['recstart']);
                        print "</td>";
                    print "</tr>"; 
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                            print "End Date Date";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                            print date("m/d/Y",$forminfo['recstart']);
                        print "</td>";
                    print "</tr>"; 
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                        print "<table class='clear'>";
                        print "<tr>";
                            print "<td class='centered-33'>";
                                print "Units";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "Rate";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "Freq";
                            print "</td>";
                        print "</tr>";
                        print "</table>";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                        print "<table class='clear'>";
                        print "<tr>";
                            print "<td class='centered-33'>";
                                print $forminfo['units'];
                            print "</td>";
                            print "<td class='centered-33'>";
                                print $forminfo['rate'];
                            print "</td>";
                            print "<td class='centered-33'>";
                                print $forminfo['freq'];
                            print "</td>";
                        print "</tr>";
                        print "</table>";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class=\"rec-label-above\">";
                            print "Work Performed";
                        print "</td>";
                    print "</tr>"; 
                    print "<tr>";
                        print "<td class=\"centered\">";
                            print $forminfo['work'];
                        print "</td>";
                    print "</tr>";
                    print "<tr><td class='centered'>&nbsp;</td></tr>";
                }
                
                // --> Nav buttons
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-2-left'>";
                            print "<input class='btn-bk' type='submit' name='btnConfirm' value='Confirm' />";
                        print "</td>";
                        print "<td class='submit-2-right'>";
                            print "<input class='btn-rd' type='submit' name='btnCancel' value='Cancel' />";
                        print "</td>";
                    print "</tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>";
                
                break;
            
            default:
                // --> Set incident number as hidden field if modifying
                if ( $display_type == "MODIFY" ) {
                    print "<input type='hidden' name ='incno' value='".$incno."' />";
                }
                // --> Choose type
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='select-line-left'>";
                            print "<input name='etype' type='radio' checked='checked' value='std' />&nbsp;Incident";
                        print "</td>";
                        print "<td class='select-line-right'>";
                            print "<input name='etype' type='radio' value='rec' />&nbsp;Recurring";
                        print "</td>";
                    print "</tr>"; 
                    print "</table>";
                    print "</td>";
                print "</tr>"; 
                print "<tr class='std'>";
                    print "<td class='input-label-above'>";
                        print "Incident Date";
                    print "</td>";
                print "</tr>";    
                print "<tr class='std'>";
                    print "<td class='centered'>";
                    enter_date($forminfo['incdate'],"_inc");
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Company Name";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<select size='1' name='custid'>";
                        while ($cust_select = $cust_list->fetch(PDO::FETCH_ASSOC)) {
                            if ( $forminfo['custid']==$cust_select['custid'] ) {
                                print "<option selected value='".$cust_select['custid']."'>".$cust_select['cname']."</option>";
                            }
                            else {
                            print "<option value='".$cust_select['custid']."'>".$cust_select['cname']."</option>";                             
                            }
                        }
                        print "</select>";
                    print "</td>";
                print "</tr>"; 
                  print "<tr>";
                      print "<td class=\"input-label-above\">";
                          print "Requested By";
                      print "</td>";
                  print "</tr>"; 
                  print "<tr>";
                      print "<td class=\"centered\">";
                          print "<input class=\"filled-95\" type=\"text\" name=\"reqby\" value=\"".$forminfo['reqby']."\" />";
                      print "</td>";
                  print "</tr>"; 
              print "<tr>";
                  print "<td class=\"input-label-above\">";
                      print "Rate";
                  print "</td>";
              print "</tr>"; 
              print "<tr>";
                  print "<td class=\"centered\">";
                      print "<input class=\"filled-95\" type=\"number\" name=\"hourlyrate\" value=\"".$forminfo['hourlyrate']."\" />";
                  print "</td>";
              print "</tr>"; 
                print "<tr>";
                    print "<td class=\"input-label-above\">";
                        print "Equipment To Service";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"centered\">";
                        print "<input class=\"filled-95\" type=\"text\" name=\"equipment\" value=\"".$forminfo['equipment']."\" />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"input-label-above\">";
                        print "Equipment Location";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"centered\">";
                        print "<input class=\"filled-95\" type=\"text\" name=\"location\" value=\"".$forminfo['location']."\" />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"input-label-above\">";
                        print "Contact Phone";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"centered\">";
                        print "<input class=\"filled-95\" type=\"text\" name=\"phone\" value=\"".$forminfo['phone']."\" />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"input-label-above\">";
                        print "Issue";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"centered\">";
                        print "<textarea class=\"filled-95\" rows=\"5\" name=\"issue\">".$forminfo['issue']."</textarea>";
                    print "</td>";
                print "</tr>";
                
                // --> Entries for recurring billing
                print "<tr class='recurring'>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='header-page-label'>Recurring Entries</td>";
                    print "</tr>";
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                            print "Start Date";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                        enter_date($forminfo['recstart'],"_start");
                        print "</td>";
                    print "</tr>"; 
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                            print "End Date Date";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                        enter_date($forminfo['recend'],"_end");
                        print "</td>";
                    print "</tr>"; 
                    print "<tr>";
                        print "<td class='rec-label-above'>";
                        print "<table class='clear'>";
                        print "<tr>";
                            print "<td class='centered-33'>";
                                print "Units";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "Rate";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "Freq";
                            print "</td>";
                        print "</tr>";
                        print "</table>";
                        print "</td>";
                    print "</tr>";    
                    print "<tr>";
                        print "<td class='centered'>";
                        print "<table class='clear'>";
                        print "<tr>";
                            print "<td class='centered-33'>";
                                print "<input class=\"filled-95\" type=\"text\" name=\"units\" value=\"".$forminfo['units']."\" />";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "<input class=\"filled-95\" type=\"text\" name=\"rate\" value=\"".$forminfo['rate']."\" />";
                            print "</td>";
                            print "<td class='centered-33'>";
                                print "<input class=\"filled-95\" type=\"text\" name=\"freq\" value=\"".$forminfo['freq']."\" />";
                            print "</td>";
                        print "</tr>";
                        print "</table>";
                        print "</td>";
                    print "</tr>";    
                print "<tr>";
                    print "<td class=\"rec-label-above\">";
                        print "Work Performed";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class=\"centered\">";
                        print "<textarea class=\"filled-95\" rows=\"5\" name=\"work\">".$forminfo['work']."</textarea>";
                    print "</td>";
                print "</tr>";
                    print "<tr><td class='centered'>&nbsp;</td></tr>";
                    print "</table>";
                    print "</td>";
                print "</tr>"; 
                
                // --> Nav buttons
                
                // --> Set button information
                if ( $display_type == "MODIFY" ) { 
                    $btn_name = "btnSave"; $btn_value = "Save";
                }
                else {
                    $btn_name = "btnCreate"; $btn_value = "Create";
                }
                 
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-2-left'>";
                            print "<input class='btn-bk' type='submit' name='".$btn_name."' value='".$btn_value."' />";
                        print "</td>";
                        print "<td class='submit-2-right'>";
                            print "<input class='btn-rd' type='submit' name='btnCancel' value='Cancel' />";
                        print "</td>";
                    print "</tr>";
                    print "</table>";
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
<script type="text/javascript" defer="defer">
$(document).ready(function() {
$("input[name$='etype']").click(function() {
        var etype = $(this).val();
        if (etype == "std") {
            $(".recurring").hide();
            $(".std").show();
        } else {
            $(".recurring").show();
            $(".std").hide();
        }
    });
});
</script>    
</body>
</html>