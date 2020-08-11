<?PHP

function active_list_control($start,$page_size,$page_name) {
// page_size  - Number of records to display per page
// page_name  - Name of the page to load
    
// --> Calc next and back page numbers
$back = $start - $page_size;
$next = $start + $page_size;

// --> Set the link address
if ( $page_name == "/tickets.php" ) { $link = "/tickets/"; }
if ( $page_name == "/worksheet.php" ) { $link = "/worksheet/edit/".$_SESSION['incno']."/"; }

	// Check to see if there are enough records to page.
	if($_SESSION['nume'] > $page_size )
	{ 
		// Start the bottom links with Prev and next link with page numbers
		
		// This starts the table and sets the left cell
		echo "<table align = 'center' width='80%'><tr><td  align='left' width='20%'>";
		
		// if our variable $back is equal to or more than 0 display the link to move back
		if($_SESSION['back'] >=0) 
		{ 
			// Set the hyperlink to page backwards. 
			print "<a href='".$link.$back."'><img border=\"0\" width=\"35\" height=\"35\" src=\"/images/LeftArrow.gif\"></a>"; 
		}
	
		// Let us display the page links at  center. We will not display the current page as a link
		
		// Start the center cell
		echo "</td><td align=center width=\"60%\">";
		
		// St variables for the FOR loop. i
		$i=0;
		$l=1;
		
		// Run the FOR loop to process
		for( $i=0; $i < $_SESSION['nume']; $i=$i+$page_size )
		{
			// Check to see if $i is equal to the selected page. If not allow a hyperlink for direct access
			if($i <> $_SESSION['eu'])
			{ 
				// Display page number as hyperlink for direct access
				echo " <a href=\"".$page_name."?start=".$i."\"><font face=\"Verdana\" size=\"2\">".$l."</font></a> "; 
			}
			else 
			{ 
				// This is the current page is not displayed as link and given font color red
				echo "<font face=\"Verdana\" size=\"2\" color=red>".$l."</font>";
			}        
			
			// Add one for processing
			$l++;
		}
	
		// Set of the right cell
		echo "</td><td align=\"right\" width=\"20%\">";
		
		// If we are not in the last page then Next link will be displayed. Here we check that
		if ( $_SESSION['this1'] < $_SESSION['nume'] ) 
		{ 
			// Print 'Next' as a hyperlink
			print "<a href=\"".$link.$next."\"><img border=\"0\" width=\"35\" height=\"35\" src=\"/images/RightArrow.gif\"></a>";
		} 
		
		// End the table that display the page navigation
		echo "</td></tr><tr><td colspan=\"3\">&nbsp;</td></tr></table>";
	
	}

}

function active_list_find($table_name,$act_query,$page_ref,$start_rec,$page_size, $begYear = "2015")
{
// Function to find records for display 

// table_name - The table to be queried
// act_query  - Used if the query is not for all records
// page_ref   - The name of the page this is ised in
// start_rec  - The starting record number
// page_size  - Number of records to display per page

	// --> Database connection
  $conn = pdo_connect();
  
	// Setup the query string
  switch ($act_query)
  {
    case "CUSTOM00":
      $query = "SELECT " .
        "incidents.incno AS incno, ".
        "incidents.incdate AS incdate, ".
        "incidents.issue AS issue, ".
        "incidents.finished AS finished, ".
        "incidents.invno AS invno, ".
        "customers.cname AS cname, ".
        "invoices.invbal AS invbal, ".
        "invoices.invtotal AS invtotal ".
        "FROM incidents ".
        "LEFT JOIN customers ON incidents.custid = customers.custid ".
        "LEFT JOIN invoices ON incidents.invno=invoices.invno ".
        "WHERE invdate >= '" . $begYear . "-01-01' " .
        "ORDER BY incidents.incdate DESC,incidents.incno DESC ";
        break;

      case "CUSTOM01":
        $query = "SELECT ".
          "logid, ".
          "workdate, ".
          "workperf, ".
          "timein, ".
          "timeout, ".
          "units, ".
          "rate, ".
          "(units * rate) AS line_total ".
          "FROM worklog WHERE incno = ".$table_name;
          break;

      case "CUSTOM02":
        $query = "Select sum(units * rate) AS grand_total FROM worklog WHERE incno = ".$table_name;
        break;

      case "CUSTOM03":
        $query = "SELECT ".
          "incidents.incno AS incno, ".
          "incidents.incdate AS incdate, ".
          "incidents.issue AS issue, ".
          "incidents.finished AS finished, ".
          "incidents.invno AS invno, ".
          "customers.cname AS cname, ".
          "invoices.invbal AS invbal, ".
          "invoices.invtotal AS invtotal ".
          "FROM incidents ".
          "LEFT JOIN customers ON incidents.custid = customers.custid ".
          "LEFT JOIN invoices ON incidents.invno=invoices.invno ".
          "WHERE invoices.paiddate IS NULL ".
          "AND invoices.invdate > '2014-01-01' ".
          "AND incidents.finished < 4 ".
          "ORDER BY incidents.incdate DESC,incidents.incno DESC ";
          break;

      case "CUSTOM04":
        $query = "SELECT ".
          "incidents.incno AS incno, ".
          "incidents.incdate AS incdate, ".
          "incidents.issue AS issue, ".
          "incidents.finished AS finished, ".
          "incidents.invno AS invno, ".
          "customers.cname AS cname, ".
          "invoices.invbal AS invbal, ".
          "invoices.invtotal AS invtotal ".
          "FROM incidents ".
          "LEFT JOIN customers ON incidents.custid = customers.custid ".
          "LEFT JOIN invoices ON incidents.invno=invoices.invno ".
          "WHERE invoices.paiddate IS NULL ".
          "AND incidents.finished = 0 ".
          "ORDER BY incidents.incdate DESC,incidents.incno DESC ";
          break;

      default:
        $query = "SELECT * FROM ".$table_name." ".$act_query;
  }
// print_r($query); exit;
	// WE have to find out the number of records in our table. We will use this to break the pages
	
	// Copy original query to a 2nd query for the purpose of determining the number of records
	$query2=$query;
	
  // Run the query
  try {
    $result2 = $conn->query($query2);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

	// Store the number of records returned to a variable that will be used in processing
	$_SESSION['nume']=count($result2->fetchAll());

  // Set up variables used in the display/processing of the records
	
	// The name of this page so the program knows what page to reload during processing 
	$page_name=$page_ref;
	
	// This variable holds the starting number of the record in the table to display
	$start = $start_rec;
	
	// Variable that holds the starting record number and is used throughout the program.
	$eu = ($start - 0); 
	$_SESSION['eu'] = $eu; 
	
	// The number of records to display per page
	$limit = $page_size;
        $_SESSION['limit'] = $limit;
	
	// Not sure but think this is a maximum limit
	$_SESSION['this1'] = $eu + $limit; 
	
	// Record number (in the table) of record to start with if 'Back' is selected
	$_SESSION['back'] = $eu - $limit; 
	
	// Record number (in the table) of the record to start with if 'Next' is selected 
	$_SESSION['next'] = $eu + $limit; 
	
	// This query strings runs with the limits in place and only pulls the records from the starting point plus the limit.
	$query = $query." LIMIT $eu, $limit";
	
  // Run the query
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }
        
	return $result;   

}