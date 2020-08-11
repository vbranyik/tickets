<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Login screen
// --> Last Edit    : May 28, 2020
// --> Last Edit    :

session_start();

/*
 * Get system settings
 */
require_once dirname(__DIR__) . "/settings.php";

// --> Load paths based on server and then load libraries 
// require $sys_code_path . "/system.php";

// Remove for settings.php change
//  $sys_path = set_paths();

require $sys_path['code'] . "/lib-login.php";
require $sys_path['code'] . "/lib-time.php";

// Set error code to zero
$error_msg = "";

// --> Check post variables
if ( isset($_POST['txtUser']) ) { 
  $user = $_POST['txtUser']; 
}
else {
  $user = "";
}

if ( isset($_POST['btnLogin']) )
{
	// Make sure the user field is blank - set error message
	if ( trim($_POST['txtUser']) == "" ) { $error_msg = "User name is blank"; }
	
	// Check to see if the password field is blank
	if ( trim($_POST['txtPassword']) == "" )
	{
        // --> Just the Password field is blank
        if ( $error_msg = "" ) { $error_msg = "Password is blank"; } else { $error_msg = "<br />Password is blank"; }
	}

	// If the password is not blank find the user and authenticate
	if ( $error_msg == "" ){
    // --> Attempt to log in user and return code with status
    $attempt = sign_in(trim($_POST['txtUser']),trim($_POST['txtPassword']));

    // --> Parse the return code from login. Redirect to main page on success, set error on fail
    if ($attempt == "VALID" ) {
      header("Location: /tickets.php");
      exit; 
    }
    else { 
      $error_msg = "Invalid Login"; 
    }
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
	
    <title>Branyik Consulting | Ticket System | Login</title>
</head>
<script>function setFocus() { document.getElementById("start").focus(); }</script>
<body onload="setFocus()">
<table class="clear"><tr><td>&nbsp;</td></tr></table>
<form name="Login" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST">
<table class="main">
<tr><td class="whitespace-20">&nbsp;</td></tr>
<tr><td class="title-header"><img border="0" src="/images/banner-cp.png" /></td></tr>
<tr><td class="whitespace-20">&nbsp;</td></tr>
<tr><td class="title-sub-header">Login to System</td></tr>
<?php if ( trim($error_msg) != "" ) { print "<tr><td class=\"error\">".$error_msg."</td></tr>"; } ?>
<tr>
	<td>
		<table class="clear">
		<tr>
			<td class="user-login-left">User Name:</td>
			<td class="user-login-right"><input id="start" type="text" name="txtUser" size="20" value="<?php print $user ?>" /></td>
		</tr>
		<tr>
			<td class="user-login-left">Password:</td>
			<td class="user-login-right"><input type="password" name="txtPassword" size="20" /></td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td align="center">
	<table width="100%" id="tblSubmit">
		<tr valign="top">
            <td class="submit-2-left"><input class="btn-bk" type="submit" name="btnLogin" value="Login" /></td>
    		<td class="submit-2-right"><input class="btn-rd" type="submit" name="btnCancel" value="Cancel" /></td>
        </tr>
	</table>
	</td>
</tr>
<tr><td class="whitespace-50">&nbsp;</td></tr>
</table>
</form>
</body>
</html>