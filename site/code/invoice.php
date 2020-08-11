<?php

// --> Generate an invoice
function gen_invoice($incno,$type)
{
    // --> Connect to database
    $conn = pdo_connect();
    
    switch($type){
      case "customer":
        // --> Set query to get CUSTOMER information
        $cust_query = "SELECT ".
          "customers.custid AS custid, ".
          "customers.cname AS cname, ".
          "customers.street AS street, ".
          "customers.city AS city, ".
          "customers.state AS state, ".
          "customers.zip AS zip, ".
          "customers.terms AS terms ".
          "FROM incidents RIGHT JOIN customers ON incidents.custid = customers.custid WHERE incidents.incno = ".$incno;

        // --> Run the query for CUSTOMER information
        try {
          $result = $conn->query($cust_query);
        }
        catch (PDOException $e) {
          echo 'Connection failed:<br />Query:' . $cust_query . "<br />" . $e->getMessage();
        }

        // --> End section
        break;
         
        case "incident":
          // --> Set query to get CUSTOMER information
          $incident_query = "SELECT ".
            "incidents.incdate AS incdate, ".
            "incidents.reqby AS reqby, ".
            "incidents.serviceloc AS serviceloc, ".
            "incidents.equipment AS equipment, ".
            "incidents.incdate AS incdate, ".
            "incidents.issue AS issue, ".
            "incidents.invno AS invno, ".
            "incidents.finished AS finished ".
            "FROM incidents WHERE incidents.incno = ".$incno;

          // Run the query
          try {
            $result = $conn->query($incident_query);
          }
          catch (PDOException $e) {
            echo 'Connection failed:<br />Query:' . $incident_query . "<br />" . $e->getMessage();
          }

          // --> End section
          break;
         
        case "work":
          // --> Get the WORK LOG information for this incident
          $work_query = "SELECT ".
            "workdate, ".
            "workperf, ".
            "units, ".
            "rate, ".
            "(units * rate) AS line_total  ".
            "FROM worklog WHERE incno = ".$incno;

          // --> Run query for WORK LOG entries attached to the incident
          try {
            $result = $conn->query($work_query);
          }
          catch (PDOException $e) {
            echo 'Connection failed:<br />Query:' . $work_query . "<br />" . $e->getMessage();
          }
        
          // --> End section
          break;

        case "total":
          // --> Set query to get the GRAND TOTAL for this invoice
          $total_query = "SELECT sum(units * rate) AS grand_total FROM worklog WHERE incno = ".$incno;

          // --> Run query for GRAND TOTAL
          try {
            $result = $conn->query($query);
          }
          catch (PDOException $e) {
            echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
          }

          // --> End section
          break;
    }

    // --> Return information
    return $result;
}

// --> Display invoice
function print_invoice($where)
{
    // --> Get Work Log
    $work_result = gen_invoice($_SESSION['incno'],'work');

    // --> Get Incident information
    $incident_info = gen_invoice($_SESSION['incno'],'incident')->fetch(PDO::FETCH_ASSOC);;

    // --> Get Invoice number for display
    $_SESSION['invno'] = $incident_info['invno'];

    // --> Start PDF
    class PDF extends FPDF
    {
        // Page header
        function Header()
        {
            // Logo
            $this->Image('/home4/vbranyik/webs/tickets/images/print/logo-header.png',30,5);

            // Line break
            $this->Ln(20);

            // --> Get customer information
            $cust_info = gen_invoice($_SESSION['incno'],'customer')->fetch(PDO::FETCH_ASSOC);

            // --> Customer Billing information

            // --> Set print position and print invoice label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Invoice Number",0,0,'R');

            // --> Set print position and print invoice number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$_SESSION['invno'],0,1,'R');

            // --> Set print position and print Company name
            $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$cust_info['cname'],0,0,'L');

            // --> Set print position and print Invoice Date label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Date",0,0,'R');

            // --> Set print position and print Invoice Date number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,"10/14/14",0,1,'R');

            // --> Set print position and print Street
            $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$cust_info['street'],0,0,'L');

            // --> Set print position and print Terms label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Terms",0,0,'R');

            // --> Set print position and print Terms information
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$cust_info['terms'],0,1,'R');

            // --> Set print position and print City, State, Zip
            $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$cust_info['city'].", ".$cust_info['state']." ".$cust_info['zip'],0,0,'L');

            // --> Set print position and print Customer Number label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Customer Number",0,0,'R');

            // --> Set print position and print Customer number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$cust_info['custid'],0,1,'R');

            // --> Set print position and print Incident Number label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Ticket Number",0,0,'R');

            // --> Set print position and print Customer number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$_SESSION['incno'],0,1,'R');

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
            // --> Get customer information
            $total = gen_invoice($_SESSION['incno'],'total')->fetch(PDO::FETCH_ASSOC);

            // --> Get the remittance address
            $remit = address_for_remittal();

            // --> Print Horizontal line above footer
            $this->Line(10,250,205,250);

            // Position at 1.5 cm from bottom
            $this->SetY(-30);
            // Remit to label
            $this->SetFont('Arial','B',10); $this->Cell(30,7,"Please remit payment to:",0,0,'L');

            // --> Print GRAND TOTAL label
            $this->SetX(-50); $this->Cell(18,7,"Invoice Total:",0,0,"R");

            // --> Print GRAND TOTAL
            $this->SetX(-30); $this->Cell(18,7,number_format($total['grand_total'],2),0,1,"R");

            // Remit name
            $this->SetFont('Arial','',10); $this->Cell(30,5,"Vilmos Branyik",0,0,'L');

            // --> Set print position and print invoice label
            $this->SetX(-50); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Invoice Number",0,0,'R');

            // --> Set print position and print invoice number
            $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$_SESSION['invno'],0,1,'R');

            // Remit Street
            $this->SetFont('Arial','',10); $this->Cell(30,5,$remit['street'],0,0,'L');

            // --> Set print position and print invoice label
            $this->SetX(-50); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Ticket Number",0,0,'R');

            // --> Set print position and print invoice number
            $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$_SESSION['incno'],0,1,'R');

            // Remit City, State, Zip
            $this->SetFont('Arial','',10); $this->Cell(30,5,$remit['city'],0,0,'L');

            // Print page number. Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',8);
            // Page number
            $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }

    // Initialize PDF page
    $pdf = new PDF();

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

    // --> Display PDF
    $pdf->Output($_SESSION['invno'].".pdf",$where);
   
}

