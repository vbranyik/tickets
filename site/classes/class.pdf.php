<?php
/**
 * Description of pdf
 *
 * @author matyas
 */
// --> Start PDF
class PDF extends FPDF
{
    // Declare variables
    var $incno; 
    var $total;
    
    // Declare arrays
    public $cust_info = array();
    public $incident_info = array();
    
    // --> Set the incident number
    public function set_incident($incnum) {
        $this->incno = $incnum;
    }
    
    
    // Page header
    function Header()
    {
        // --> Set paths
        if ( substr($_SERVER['HTTP_HOST'],-5) == ".test" ) {
            // --> Running locally
            $sys_path = "/data/Dropbox/Active-Sites/tickets/v1.1";

        } else {
            // --> Running on Public server
            $sys_path = "/home4/vbranyik/webs/tickets";

        }

        // --> Get customer information
        $this->cust_info = gen_invoice($this->incno,'customer')->fetch(PDO::FETCH_ASSOC);

        // --> Get Incident information
        $this->incident_info = gen_invoice($this->incno,'incident')->fetch(PDO::FETCH_ASSOC);

        // Logo
        $this->Image($sys_path.'/images/print/logo-header.png',30,5);

        // Line break
        $this->Ln(20);

        // --> Customer Billing information

        // --> Set print position and print invoice label
        $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Invoice Number",0,0,'R');

        // --> Set print position and print invoice number
        $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$this->incident_info['invno'],0,1,'R');

        // --> Set print position and print Company name
        $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$this->cust_info['cname'],0,0,'L');

        // --> Set print position and print Invoice Date label
        $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Date",0,0,'R');

        // --> Set print position and print Invoice Date number
        $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,date("m/d/Y",strtotime($this->incident_info['invdate'])),0,1,'R');

        // --> Set print position and print Street
        $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$this->cust_info['street'],0,0,'L');

        // --> Set print position and print Terms label
        $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Terms",0,0,'R');

        // --> Set print position and print Terms information
        $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$this->cust_info['terms'],0,1,'R');

        // --> Set print position and print City, State, Zip
        $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$this->cust_info['city'].", ".$this->cust_info['state']." ".$this->cust_info['zip'],0,0,'L');

        // --> Set print position and print Customer Number label
        $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Customer Number",0,0,'R');

        // --> Set print position and print Customer number
        $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$this->cust_info['custid'],0,1,'R');

        // --> Set print position and print Incident Number label
        $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Ticket Number",0,0,'R');

        // --> Set print position and print Customer number
        $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$this->incno,0,1,'R');

        // --> Set position for detail display
        $this->SetY(58);

        // --> Print work display header detail
        $this->SetFillColor(200,200,200);
        $this->SetFont('Arial','B',10);
        $this->SetX(10); $this->Cell(26,5,"Date",0,0,"C","true");
        $this->SetX(35); $this->Cell(125,5,"Work Performed",0,0,"C","true");
        $this->SetX(160); $this->Cell(12,5,"Unit(s)",0,0,"C","true");
        $this->SetX(172); $this->Cell(13,5,"Rate",0,0,"C","true");
        $this->SetX(185); $this->Cell(20,5,"Total",0,1,"C","true");

        // --> Print Horizontal line on top and bottom of header line
        $this->Line(10,63,205,63); $this->Line(10,58,205,58);
     }

    // Page footer
    function Footer()
    {
        // --> Get remittance address
        $remit_to = address_for_remittal();
      
        // --> Get Incident information
        $this->incident_info = gen_invoice($this->incno,'incident')->fetch(PDO::FETCH_ASSOC);

        // --> Get customer information
        $this->total = gen_invoice($this->incno,'total')->fetch(PDO::FETCH_ASSOC);

        // --> Print Horizontal line above footer
        $this->Line(10,250,205,250);

        // Position at 1.5 cm from bottom
        $this->SetY(-30);
        // Remit to label
        $this->SetFont('Arial','B',10); $this->Cell(30,7,"Please remit payment to:",0,0,'L');

        // --> Print GRAND TOTAL label
        $this->SetX(-50); $this->Cell(18,7,"Invoice Total:",0,0,"R");

        // --> Print GRAND TOTAL
        $this->SetX(-30); $this->Cell(18,7,number_format($this->total['grand_total'],2),0,1,"R");

        // Remit name
        $this->SetFont('Arial','',10); $this->Cell(30,5,"Vilmos Branyik",0,0,'L');

        // --> Set print position and print invoice label
        $this->SetX(-50); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Invoice Number",0,0,'R');

        // --> Set print position and print invoice number
        $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$this->incident_info['invno'],0,1,'R');

        // Remit Street
        $this->SetFont('Arial','',10); $this->Cell(30,5,$remit_to['street'],0,0,'L');

        // --> Set print position and print invoice label
        $this->SetX(-50); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Ticket Number",0,0,'R');

        // --> Set print position and print invoice number
        $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$this->incno,0,1,'R');

        // Remit City, State, Zip
        $this->SetFont('Arial','',10); $this->Cell(30,5,$remit_to['city'],0,0,'L');

        // Print PayPal Information. Position at 3.0 cm from bottom
        $this->SetY(-30);
        // Arial italic 8
        $this->SetFont('Arial','B',12);
        // Page number
        $this->Cell(0,10,'To pay via PayPal',0,0,'C');

        $this->SetY(-25);
        // Arial italic 8
        $this->SetFont('Arial','',12);
        // Page number
        $this->Cell(0,10,'vilmos@branyik.com',0,0,'C');

        // Print page number. Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

?>
