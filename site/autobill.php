<?php
session_start();

/*
 * Note: This cannot be run from command line because session variables cannot
 *       be used when run from command line.
 */

// --> Set paths
require_once dirname(__DIR__) . "/settings.php";

//  if ( substr($_SERVER['HTTP_HOST'],-5) == ".test" ) {
//      // --> Running locally
//      $sys_classes_path = "/data/Dropbox/Active-Sites/tickets/v1.1/classes";
//      $sys_code_path = "/data/Dropbox/Active-Sites/tickets/v1.1/code";
//      $sys_fonts_path = "/data/Dropbox/Active-Sites/tickets/v1.1/fonts";
//      $sys_mandrill_path = "/data/Dropbox/Active-Sites/shared/mandrill";
//      $sys_pdf_path = "/data/Dropbox/Active-Sites/tickets";
//
//      // --> Database connect profile
//      require_once "/data/Dropbox/Active-Sites/tickets/v1.1/_dbconnect/tickets.dev.php";
//
//      // --> Set send invoice to no
//      // $send_invoice = "NO";
//  } else {
//      // --> Running on Public server
//      $sys_classes_path = "/home4/vbranyik/webs/tickets/classes";
//      $sys_code_path = "/home4/vbranyik/webs/tickets/code";
//      $sys_fonts_path = "/home4/vbranyik/webs/tickets/fonts";
//      $sys_mandrill_path = "/home4/vbranyik//public_code/shared/mandrill";
//      $sys_pdf_path = "/home4/vbranyik/tickets";
//
//      // --> Database connect profile
//      require_once "/home4/vbranyik/dbinfo/tickets.php";
//  }

// --> Required libraries for FPDF
define('FPDF_FONTPATH', $sys_path['fonts'] . '/');
require_once $sys_path['classes'] . '/fpdf.php';
require_once $sys_path['classes'] . '/class.phpmailer.php';
require_once $sys_path['classes'] . '/class.pdf.php';

// --> Required libraries
require_once $sys_path['code'] . "/lib-autobill.php";
require_once $sys_path['code'] . "/lib-browse.php";
require_once $sys_path['code'] . "/lib-customer.php";
require_once $sys_path['code'] . "/lib-incident.php";
require_once $sys_path['code'] . "/lib-invoice.php";
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";
require_once $sys_path['code'] . "/lib-worklog.php";
require_once $sys_path['mandrill'] . "/Mandrill.php";

// --> Set error message to blank
$error_message = "";

// --> Define arrays
$bts = array();
$preview = array();

// --> Set newline code 
$nl = "<br />";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-5) != ".test" ) {
    check_auth(0);
}

// --> Get the recurring for today's date
// -->  $bts = get_bills(); SELECT * FROM `autobill` WHERE nextbill='2016-12-22'
// --> Connect to database
$conn = pdo_connect();

// --> Set update query to change incident status incident
// $query = "SELECT * FROM autobill WHERE nextbill='".date("Y-m-d")."' ORDER BY custid";
$query = "SELECT * FROM autobill WHERE nextbill='".date("Y-m-d")."' ORDER BY custid";

// --> Run query to get INCIDENT information
try {
  $result = $conn->query($query);
}
catch (PDOException $e) {
  echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
}

while ($b = $result->fetch(PDO::FETCH_ASSOC)) { $bts[] = $b; $preview[] = $b; }

// --> Loop to process the returned billings
for ($z=0; $z<count($bts); $z++) {
    // --> Put incident information into array
    $incinfo['etype'] = "auto";
    $incinfo['incdate'] = $bts[$z]['nextbill'];
    $incinfo['reqby'] = $bts[$z]['reqby'];
    $incinfo['custid'] = $bts[$z]['custid'];
    $incinfo['location'] = $bts[$z]['location'];
    $incinfo['equipment'] = $bts[$z]['equipment'];
    $incinfo['phone'] = $bts[$z]['phone'];
    $incinfo['issue'] = $bts[$z]['issue'];

    // --> Create the incident
    $incno = mod_incident($incinfo,"ADD");

    // --> Set session variable for printing if not already set
    $_SESSION['incno'] = $incno;   
    
    // --> Create variable for loop
    $y = $z;
    
    // --> Check if this is the last record
    if ( $z < (count($bts)-1) ) {
        // --> Set variables for loop
        $x = 1;

        // --> Loop while the customer ID number stays the same
        while ( $bts[$y]['custid']==$bts[$y+1]['custid'] ) {
            // --> Increment the counters
            $x++;
            $y++;

            // --> Check if this is the last record, if so exit loop
            if ( $y < (count($bts)-1) ) { break; }
        }
    } else {
        // --> This is the last record on the array
        $x = 1;
    }

    // --> Add line items to bill
    for ($a=1; $a<=$x; $a++) {
        // --> Put line information into an array
        $lineinfo['incno'] = $incno;
        $lineinfo['txt_year_log'] = substr($bts[$z+($a-1)]['nextbill'],0,4);
        $lineinfo['txt_month_log'] = substr($bts[$z+($a-1)]['nextbill'],5,2);
        $lineinfo['txt_day_log'] = substr($bts[$z+($a-1)]['nextbill'],8,2);
        $lineinfo['workperf'] = $bts[$z+($a-1)]['workperf'];
        $lineinfo['txt_hour_in'] = date("H");
        $lineinfo['txt_minute_in'] = date("i");
        $lineinfo['txt_hour_out'] = date("H");
        $lineinfo['txt_minute_out'] = date("i");
        $lineinfo['units'] = $bts[$z+($a-1)]['units'];
        $lineinfo['rate'] = $bts[$z+($a-1)]['rate'];
        $lineinfo['autoid'] = $bts[$z+($a-1)]['autoid'];
        $lineinfo['freq'] = $bts[$z+($a-1)]['freq'];
        $lineinfo['nextbill'] = $bts[$z+($a-1)]['nextbill'];
        $lineinfo['expire'] = $bts[$z+($a-1)]['expire'];
        $lineinfo['txt_hour_in'] = "00";
        $lineinfo['txt_minute_in'] = "00";
        $lineinfo['txt_ampm_in'] = "AM";
        $lineinfo['txt_hour_out'] = "00";
        $lineinfo['txt_minute_out'] = "00";
        $lineinfo['txt_ampm_out'] = "AM";
        
        // --> Add line item
        mod_worklog($lineinfo,"ADD");
        
        // --> Set nextbill date
        $new_next_bill = strtotime(date("Y-m-d",strtotime('+'.$lineinfo['freq']." months",strtotime($lineinfo['nextbill']))));
        
        // --> Check to see if the new next bill is before the expire
        // --> if so set new netxbill date
        if ($new_next_bill <= strtotime($lineinfo['expire'])) {
            set_next_bill($lineinfo['autoid'],$new_next_bill);
        }
        
    }

    // --> Close the invoice
    close_incident($incno,1);

    // --> Send the invoice

    // --> Get invoice total
    $invtotal = gen_invoice($incno,"total")->fetch(PDO::FETCH_ASSOC);

    // --> Insert invoice detail
    $invno = insert_invoice($incno,$incinfo['custid'],$invtotal['grand_total']);

    // --> Update incident with invoice detail
    add_invoice_info($incno,$invno,2);

    // Start send invoice    

    // --> Create the PDF file

    // --> Get Work Log
    $work_result = gen_invoice($incno,'work');

    // --> Get Incident information
    $incident_info = gen_invoice($incno,'incident')->fetch(PDO::FETCH_ASSOC);

    // --> Get Invoice number for display
    // --> $incident_info['invno'] = $incident_info['invno'];

    
    // Initialize PDF page
    $pdf = new PDF();
    
    // --> Set the incident number
    $pdf->set_incident($incno);

    // --> Set up page numbering
    $pdf->AliasNbPages();

    // --> Create page size
    $pdf->AddPage("portrait","letter");

    // --> Set the page break
    $pdf->SetAutoPageBreak("auto",35);

    // --> Set position for detail display
    $pdf->SetY(65);

    $yl = 65;

    // --> Set font for display
    $pdf->SetFont('Arial','',10);

    while ($item =  $work_result->fetch(PDO::FETCH_ASSOC))
    {
        // -->Get current line position 
        $yu = $pdf->GetY();

        // --> Set line position
        $pdf->SetY($yl);

        $pdf->SetX(12); $pdf->Cell(24,5,date("m/d/Y",strtotime($item['workdate'])),0,0,"L");
        $pdf->SetX(160); $pdf->Cell(10,5,$item['units'],0,0,"5");

        $pdf->SetX(172); $pdf->Cell(11,5,$item['rate'],0,0,"R");
        $pdf->SetX(185); $pdf->Cell(18,5,number_format($item['line_total'],2),0,0,"R");
        $pdf->SetX(35); $pdf->MultiCell(120,5,$item['workperf'],0,"L");

        // -->Get lower line position 
        $yl = $pdf->GetY()+1;
    }
    
    // Check to see if directory for year exists, if not create
    if ( !file_exists($sys_pdf_path."/".date("Y",strtotime($incident_info['invdate']))) ) {
        mkdir($sys_pdf_path."/".date("Y",strtotime($incident_info['invdate'])),0755); 
    }

    // --> Display PDF
    $pdf->Output($sys_pdf_path."/".date("Y",strtotime($incident_info['invdate']))."/".$incident_info['invno'].".pdf","F");
    
    // End send invoice
    
    // --> Email the invoice

    $error_message = send_invoice($invno,$incno);

    // --> Increment primary counter based on number of line items processed
    $z = $z + ($x - 1);
}

// --> Return to calling page
header("location: setauto");