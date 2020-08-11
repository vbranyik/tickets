<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Login screen
// --> Last Edit    : 11/09/14
// --> Upgrade ready:

session_start();
// --> Set paths
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

// Set error code to zero
$error_msg = "";

// --> Set defaults for display
$display_type = "DEFAULT";
$forminfo = array();

// --> User is creating an incident that needs to be confirmed
if ( isset($_POST['btnCreate']) ) { $display_type = "CREATE"; }
if ( isset($_POST['btnConfirm']) ) { $display_type = "CONFIRMED"; }

// --> If this is the default screen
if ( $display_type == "DEFAULT" ) {
    // --> Get the customer list
    $cust_list = get_cust_list(0);
    
    // --> Set default values for the form
    $forminfo['date'] = time();
    $forminfo['custid'] = "";
    $forminfo['reqby'] = "";
    $forminfo['equipment'] = "";
    $forminfo['location'] = "";
    $forminfo['phone'] = "";
    $forminfo['issue'] = "";
}

// --> If the user need to confirm creating the incident
if ( $display_type == "CREATE" ) {
    // --> Transfer POST variable
    $forminfo = $_POST;
    
    // --> Convert date string to Time interger
    $forminfo['date'] = strtotime($_POST['txt_year_inc']."-".$_POST['txt_month_inc']."-".$_POST['txt_day_inc']);
    
    // --> Get customer information
    $custinfo = get_customer($_POST['custid'])->fetch(PDO::FETCH_ASSOC);
}

// --> Create the record
if ( $display_type == "CONFIRMED" ) {
    $forminfo['incno'] = mod_incident($_POST,"ADD");
    
    // --> Check result and set display type accordingly
    if ( $forminfo['incno'] > 0 ) {
        // --> The incident was created
        $display_type = "COMPLETE";
    }
    else {
        // --> There was an error
        $display_type = "ERROR";
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
</head>
<body>
<form name="CREATEINC" action="<? $_SERVER['PHP_SELF']; ?>" method="POST">
    <p>&nbsp;</p>
    <table class="main-create">
    <tr>
        <td class="centered">
        <table class="clear">
        <tr>
            <td class="header">Incident System</td>
        </tr>
        <tr>
            <td class="header-page-label">Create Incident</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <?PHP
        switch ($display_type)
        {
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
                // --> The select type of incident box
                print "<tr>";
                    print "<td>&nbsp;</td>";
                print "</tr>";
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table>";
                    print "<tr>";
                        print "<td class='select-line-left'>";
                            print "<input name='type' type='radio' checked='checked' value='std' />&nbsp;Incident";
                        print "</td>";
                        print "<td class='select-line-right'>";
                            print "<input name='type' type='radio' value='rec' />&nbsp;Recurring";
                        print "</td>";
                    print "</tr>"; 
                    print "</table>";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td>&nbsp;</td>";
                print "</tr>";
                
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Incident Date";
                    print "</td>";
                print "</tr>"; 
                // --> Put POST variable into hidden variables to transfer to the next page
                foreach ($_POST as $key => $value) {
                    print "<input type=\"hidden\" name=\"".$key."\" value=\"".$value."\"/>"; 
                }
                
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Incident Date";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print date("m/d/Y",$forminfo['date']);
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
                
                print "<tr><td>&nbsp;</td></tr>";
                
                // --> Entries for recurring billing
                print "<div id='recurring'>";
                    print "<tr>";
                        print "<td class='header-page-label'>Recurring Entries</td>";
                    print "</tr>";
                print "</div>";
                
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
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Incident Date";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                    enter_date($forminfo['date'],"_inc");
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
                    print "<td class='input-label-above'>";
                        print "Requested By";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<input class='filled-95' type='text' name='reqby' value='".$forminfo['reqby']."' />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Equipment To Service";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<input class='filled-95' type='text' name='equipment' value='".$forminfo['equipment']."' />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Equipment Location";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<input class='filled-95' type='text' name='location' value='".$forminfo['location']."' />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Contact Phone";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<input class='filled-95' type='text' name='phone' value='".$forminfo['phone']."' />";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='input-label-above'>";
                        print "Issue";
                    print "</td>";
                print "</tr>"; 
                print "<tr>";
                    print "<td class='centered'>";
                        print "<textarea class='filled-95' rows='5' name='issue'>".$forminfo['issue']."</textarea>";
                    print "</td>";
                print "</tr>";
                
                print "<tr><td>&nbsp;</td></tr>";
                
                // --> Nav buttons
                print "<tr>";
                    print "<td class='centered'>";
                    print "<table class='clear'>";
                    print "<tr>";
                        print "<td class='submit-2-left'>";
                            print "<input class='btn-bk' type='submit' name='btnCreate' value='Create' />";
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
    $("input[name$='type']").click(function() {
        var etype = $(this).val();

        if (etype === "std") {
            $("#recurring").hide();
        } else {
            $("#recurring").show();
        }
    });
});</script>    
</body>
</html>