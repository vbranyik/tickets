<?PHP
// --> Required libraries for FPDF
define('FPDF_FONTPATH','/home4/vbranyik/webs/tickets/fonts/');
require('/home4/vbranyik/webs/tickets/classes/fpdf.php');
require_once('/home4/vbranyik/webs/tickets/classes/class.phpmailer.php');

// --> Required libraries
require('/home4/vbranyik/webs/tickets/code/lib-invoice.php');

// --> Require to attach to database
require("/home4/vbranyik/dbinfo/tickets.php");

// --> Set Incident number
$_SESSION['incno'] = 1527;

// --> This prints the Invoice (** Use "I" to display on screen **)
print_invoice(1527,"I");
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>TODO supply a title</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width">
    </head>
    <body>
        <div>Sent!</div>
    </body>
</html>
