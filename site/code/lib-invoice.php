<?php
// --> Set the remitance address
function address_for_remittal () {
    $return = array(
        "street" => "1680 Berwick Road",
        "city" => "Abingdon, IL 61410"
    );
    return $return;
}

// --> Create and invoice
function check_invoice($incno)
{
    // --> Connect to the database
    $conn = pdo_connect();
    
    // --> Set query to see if this can be invoiced
    $query = "SELECT invno,finished FROM incidents WHERE incno=".$incno;
    
    // --> Run query 
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }
    
    $invstatus = $result->fetch(PDO::FETCH_ASSOC);
    
    // --> Set default for return to NONE
    $ret_message = "NONE";
    
    // --> Check invoice status
    if ( $invstatus['finished'] > 0  ) {
        // --> Check to see if there is an invoice
        if ( $invstatus['invno'] > 0 ) { $ret_message = "INVOICED"; }
    }
    else { 
        // --> Invoice not closed
        $ret_message = "NOTCLOSED";
    }
    
    // --> Return check
    return $ret_message;
}


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
                    "customers.terms AS terms, ".
                    "customers.email AS email, ".
                    "customers.email2 AS email2 ".
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
                    "incidents.location AS location, ".
                    "incidents.equipment AS equipment, ".
//                    "incidents.incdate AS incdate, ".
                    "incidents.issue AS issue, ".
                    "incidents.summary AS summary, ".
                    "incidents.invno AS invno, ".
                    "incidents.finished AS finished, ".
                    "invoices.invdate AS invdate ".
                    "FROM incidents ".
                    "LEFT JOIN invoices ON incidents.invno=invoices.invno ".
                    "WHERE incidents.incno = ".$incno;

            // --> Run the query for CUSTOMER information
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
              $result = $conn->query($total_query);
            }
            catch (PDOException $e) {
              echo 'Connection failed:<br />Query:' . $total_query . "<br />" . $e->getMessage();
            }

            // --> End section
            break;
    }


    // --> Return information
    return $result;
    
}

// --> Get invoice information
function get_invoice($invno)
{
    // --> Connect to database
    $conn = pdo_connect();
    
    // --> Set query for record
    $query = "SELECT * FROM invoices WHERE invno=".$invno;
    
    // --> Run query for GRAND TOTAL
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }

    // --> Return result
    return $result;
}

// --> Insert invoice
function insert_invoice($incno,$custid,$total)
{
    // --> connect to database
    $conn = pdo_connect();
    
    // --> Set query to insert record
    $query = "INSERT INTO invoices SET ".
            "custid='".$custid."',".
            "incno=".$incno.",".
            "invdate='".date("Y-m-d",time())."',".
            "invtotal=".$total;
            
    // --> Run query for GRAND TOTAL
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }
    
    // --> Return invoice number
    return mysqli_insert_id($db);
}

// --> Post payment
function post_payment($incno,$invno,$pdate,$amount,$note,$paid)
{
    // --> Connect to database
    $conn = pdo_connect();
    
    // --> Set query to post payment
    $pymt_query = "INSERT INTO payments SET ".
            "pdate='".$pdate."',".
            "invno=".$invno.",".
            "incno=".$incno.",".
            "amount=".$amount.",".
            "note='".mysqli_real_escape_string($db,$note)."'";
            
    // --> Run query for GRAND TOTAL
    try {
      $result = $conn->query($pymt_query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $pymt_query . "<br />" . $e->getMessage();
    }
    
    // --> Set query to update invoice
    $inv_query = "UPDATE invoices SET ".
        "paiddate='".$pdate."',".
        "invbal=invbal-".$amount." ".
        "WHERE invno=".$invno;

    // --> Run query for GRAND TOTAL
    try {
      $result = $conn->query($inv_query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $inv_query . "<br />" . $e->getMessage();
    }
    
    // --> Update paid status
    if ( $paid == "YES" ) {
        // --> Set query to update paid status
        $query = "UPDATE incidents SET finished=3 WHERE incno=".$incno;
        
            // --> Run query for GRAND TOTAL
            try {
              $result = $conn->query($query);
            }
            catch (PDOException $e) {
              echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
            }
    }
    
}

// --> Create invoice as PDF
function print_invoice($incno,$where)
{
    // --> Get Work Log
    $work_result = gen_invoice($incno,'work');

    // --> Get Incident information
    $incident_info = gen_invoice($incno,'incident')->fetch(PDO::FETCH_ASSOC);;

    // --> Start PDF
    class PDF extends FPDF
    {
        // Page header
        function Header()
        {
            // --> Get customer information
            $cust_info = gen_invoice($_SESSION['incno'],'customer')->fetch(PDO::FETCH_ASSOC);

            // --> Get Incident information
            $incident_info = gen_invoice($_SESSION['incno'],'incident')->fetch(PDO::FETCH_ASSOC);

            // Logo
            $this->Image('/home4/vbranyik/webs/tickets/images/print/logo-header.png',30,5);

            // Line break
            $this->Ln(20);

            // --> Customer Billing information

            // --> Set print position and print invoice label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Invoice Number",0,0,'R');

            // --> Set print position and print invoice number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,$incident_info['invno'],0,1,'R');

            // --> Set print position and print Company name
            $this->SetX(25); $this->SetFont('Arial','',10); $this->Cell(30,5,$cust_info['cname'],0,0,'L');

            // --> Set print position and print Invoice Date label
            $this->SetX(-60); $this->SetFont('Arial','B',10); $this->Cell(20,5,"Date",0,0,'R');

            // --> Set print position and print Invoice Date number
            $this->SetX(-25); $this->SetFont('Arial','',10); $this->Cell(0,5,date("m/d/Y",strtotime($incident_info['invdate'])),0,1,'R');

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
            // --> Get Incident information
            $incident_info = gen_invoice($_SESSION['incno'],'incident')->fetch(PDO::FETCH_ASSOC);;

            // --> Get remittance address
            $remit = address_for_remittal();
            
            // --> Get customer information
            $total = gen_invoice($_SESSION['incno'],'total')->fetch(PDO::FETCH_ASSOC);

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
            $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$incident_info['invno'],0,1,'R');

            // Remit Street
            $this->SetFont('Arial','',10); $this->Cell(30,5,$remit['street'],0,0,'L');

            // --> Set print position and print invoice label
            $this->SetX(-50); $this->SetFont('Arial','B',10); $this->Cell(18,5,"Ticket Number",0,0,'R');

            // --> Set print position and print invoice number
            $this->SetX(-30); $this->SetFont('Arial','',10); $this->Cell(18,5,$_SESSION['incno'],0,1,'R');

            // Remit City, State, Zip
            $this->SetFont('Arial','',10); $this->Cell(30,5,$remit['city'],0,0,'L');

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
// --> End class    
    
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

    // --> Change directory based on dev or production
    if ( strpos(strtolower(getcwd()),"active-sites") > 0 ) {
        // --> Running in dev
        $sys_pdf_path = "/data/Dropbox/Active-Sites";
    } else {
        // --> Running in production
        $sys_pdf_path = "/home4/vbranyik";
    }
    
    // Check to see if the year exists
    if ( !file_exists($sys_pdf_path . "/tickets/".date("Y",strtotime($incident_info['invdate']))) ) { 
        mkdir($sys_pdf_path . "/tickets/".date("Y",strtotime($incident_info['invdate'])),0755); 
    }


    // --> Display PDF
    $pdf->Output(
        $sys_pdf_path . "/tickets/".date("Y",strtotime($incident_info['invdate']))."/".$incident_info['invno'].".pdf",
        $where
    );
   
}

// --> Email invoice
function send_invoice($invno,$incno) {

    $incinfo = gen_invoice($incno,"incident")->fetch(PDO::FETCH_ASSOC);
    
    $custinfo = gen_invoice($incno,"customer")->fetch(PDO::FETCH_ASSOC);

    $body = "Thanks very much for allowing me to be of service.\n\n";
    $body .= "Attached to this email is an invoice for the services rendered.\n";
    $body .= "Below is a job summary:\n\n";
    $body .= "Ticket number  : " . $incno."\n";
    $body .= "Ticket date    : " . date("m/d/Y",strtotime($incinfo['incdate'])) . "\n";
    $body .= "Requested by   : " . $incinfo['reqby'] . "\n";
    $body .= "Issue Reported :\n";
    $body .= $incinfo['issue'] . "\n\n";
    
    // --> Check to see if there is a summary
    if (trim($incinfo['summary']) != "") {
        $body .= "Final status   :\n";
        $body .= $incinfo['summary']."\n\n";
    }
    
    // --> Add PayPal link
    $body .= "Pay online using PayPal https://paypal.me/vbranyik \n\n";
    
    $body .= "If you require any addition information or assistance on this issue, please let me know. My primary interest is your satisfaction." . "\n\n";

    // --> Change directory based on dev or production
    if ( strpos(strtolower(getcwd()),"active-sites") > 0 ) {
        // --> Running in dev
        $sys_pdf_path = "/data/Dropbox/Active-Sites";
    } else {
        // --> Running in production
        $sys_pdf_path = "/home4/vbranyik";
    }
    
    // --> Mandrill Email Send
    // --> Set the Mandrill key for sending
    $mandrill = new Mandrill('PJxXhKA0BAcbuwsXo1tOBQ');
    
    // --> Encode file to base64 string for send
    $file_attachment = $sys_pdf_path . "/tickets/".date("Y",strtotime($incinfo['invdate']))."/".$incinfo['invno'].".pdf";
    $invoice_file = chunk_split(base64_encode(file_get_contents($file_attachment)));
    
    // --> Create the message
    $message = array(
        'text' => $body,
        'subject' => "Invoice for Ticket #".$incno." from Branyik Consulting",
        'from_email' => "info@bc-cs.com",
        'from_name' => "Branyik Consulting",
        'to' => array(
            array(
// for testing                'email' => "vilmos@branyik.com",
                'email' => $custinfo['email'],
                'name' => $custinfo['cname']
            )
        ),
        'headers' => array('Reply-To' => "info@bc-cs.com"),
        'preserve_recipients' => null,
        'attachments' => array(
            array(
                "type" => "application/pdf",
                "name" => $incinfo['invno'].".pdf",
                "content" => $invoice_file
            )
        )
    );
    
    // --> Send and retrieve result
    $sendresult = $mandrill->messages->send($message);

    // --> Return result
    return $sendresult;
}

function void_invoice($incno) {
    $conn = pdo_connect();
    
    // --> Set queries for void
    $query_inc = "update incidents SET finished=4 WHERE incno=".$incno;
    $query_inv = "update invoices SET void='Y' WHERE incno=".$incno;
    
    // --> Run queries to VOID invoice
    try {
      $result = $conn->query($query_inc);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query_inc . "<br />" . $e->getMessage();
    }
    
    try {
      $result = $conn->query($query_inv);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query_inv . "<br />" . $e->getMessage();
    }
    
    return;
    
}