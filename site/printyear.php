<?php
session_start();

// --> Load paths based on server and then load libraries 
require "code/system.php";
$sys_path = set_paths();

// --> Required libraries
require_once $sys_path['code'] . "/lib-required.php";
require_once $sys_path['code'] . "/lib-time.php";
require_once $sys_path['code'] . "/lib-yearend.php";
require_once $sys_path['classes'] . '/fpdf.php';

define('FPDF_FONTPATH',$sys_path['fonts'] . '/');

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
  check_auth(0);
}


// --> Start PDF
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // --> Set year
        $year = "2019";

        // Logo
        $this->Image('/home/matyas/vhosts/tickets/v1.1/images/print/logo-header.png',30,5);

        // Line break
        $this->Ln(20);

        // --> Print work display header detail
        $this->SetFillColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(35); $this->Cell(125, 5, "Year End Report " . $year, 0, 0, "C", "true");

        // --> Print Horizontal line on top and bottom of header line
        $this->Line(10,40,205,40); 
        $this->Line(10,45,205,45);

        // --> Header detail
        $this->SetXY(12, 40); $this->SetFont('Arial','B',10); $this->Cell(20, 5, "Date", 0, 0,'L'); 
        $this->SetXY(35, 40); $this->SetFont('Arial','B',10); $this->Cell(120,5,"Customer",0,0,'L'); 
        $this->SetXY(185, 40); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Amount",0,0,'R'); 
       
    }

    // Page footer
    function Footer()
    {
        // --> Get year that was passed
        if (isset($_GET['year'])) {
          $year = $_GET['year'];
        }

        // --> Get payments
        $year = "2019";
        $pymts = getPayments($year);

        // --> Get the total
        $total = 0;
        for ($z=0; $z<count($pymts); $z++) {
          $total += $pymts[$z]['amount'];
        } 

        // --> Print Horizontal line above footer
        $this->Line(10,250,205,250);

        $this->SetY(-30);
        // Remit to label
        // $this->SetFont('Arial','B',10); $this->Cell(30,7,"Please remit payment to:",0,0,'L');

        // --> Print GRAND TOTAL label
        $this->SetX(-50); $this->Cell(18, 7, "Total Received:", 0, 0, "R");

        // --> Print GRAND TOTAL
        $this->SetX(-30); $this->Cell(18,7,number_format($total, 2), 0, 1, "R");

      
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Remit to label
        $this->SetFont('Arial','',8); $this->Cell(30,7,"Printed: " . date("m-d-Y"),0,0,'R');

    }
}

if (isset($_GET['year'])) {
  $year = $_GET['year'];
}
else {
  "Not passed";
}
$year =  "2019";

// --> Get payments
$pymts = getPayments($year);

// --> Get the total
$total = 0;
for ($z=0; $z<count($pymts); $z++) {
  $total += $pymts[$z]['amount'];
} 

// Initialize PDF page
$pdf = new PDF();

// --> Create page size
$pdf->AddPage("portrait","letter");

// --> Set the page break
$pdf->SetAutoPageBreak("auto",35);

// --> Set position for detail display
$pdf->SetY(50);

$yl = 46;

// --> Set font for display
$pdf->SetFont('Arial','',10);

for ($z=0; $z<count($pymts); $z++) {
    // -->Get current line position 
    $yu = $pdf->GetY();

    // --> Set line position
    $pdf->SetY($yl);

    $pdf->SetX(12); $pdf->Cell(24, 5, date("m/d/Y",strtotime($pymts[$z]['pdate'])), 0, 0, "L");
    $pdf->SetX(35); $pdf->Cell(120, 5, $pymts[$z]['customer'], 0, "L");
    $pdf->SetX(185); $pdf->Cell(18, 5, number_format($pymts[$z]['amount'],2), 0, 0, "R");


    // -->Get lower line position 
    $yl = $pdf->GetY() + 5;
}

// --> Display PDF
$pdf->Output($_SESSION['invno'].".pdf", "I");



