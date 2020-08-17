<?php

function check_auth($type)
{
	// If type = 0 then check to see if logged in
	if ( $type == 0 )
	{
		// Check to see if logged in
		if ( $_SESSION['valid'] != "Y" )
		{
			// Set error message
			$_SESSION['error_msg'] = "You are not logged in";
			
			// Redirect to error page
			header("Location: /error"); exit;
		}
	}
	
	// If type = 1 then check to see if admin
	if ( $type == 1 )
	{
		// Check if admin
		if ( $_SESSION['usrid'] > 3 )
		{
			// Set error message
			$_SESSION['error_msg'] = "You are not authorized to view this page";
			
			// Redirect to error page
			header("Location: /error"); exit;
		}
	}

    
    // --> Get current page name
    $currentFile = $_SERVER["PHP_SELF"];
    $parts = Explode('/', $currentFile);
    $current_page =  $parts[count($parts) - 1];
}

function convert_post($sentinfo)
{
    // --> Return
    return $sentinfo;
}

function firstYearSelect($yearPassed = "", $type = "") {
  // --> Connect to Database
  $conn = pdo_connect();

  // --> Check type
  switch ($type) {
    case "payments":
      $table = "payments";
      $by = "pdate";
      break;
    default:
      $table = "invoices";
      $by = "invdate";
  }
  

  // --> Set query to get customer list
  $query = "SELECT " . $by . " AS date FROM " . $table . " order BY " . $by . " ASC LIMIT 1";

  // --> Run the query for CUSTOMER LIST
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }
  
  // --> Get the year of the oldest record in the DB
  $row = $result->fetch(PDO::FETCH_ASSOC);
  $firstYear = intval(date("Y",strtotime($row['date'])));
  $currentYear = intval(date("Y"));

  // --> Create the selector
  $selector = "<select id='year' onchange='setYear()' size='1' name='startYear'>";
  for ($z=$currentYear; $z>=$firstYear; $z--){
    if (strval($z) == $yearPassed) {
      $selector .= "<option value='" . $z . "' selected>" . $z . "</option>";
    }
    else {
      $selector .= "<option value='" . $z . "'>" . $z . "</option>";
    }
  }
  $selector .= "</select>";
    
  return $selector;  
}

function returnLink() {
  if(isset($_SESSION['returnURL']) || trim($_SESSION['returnURL']) !== "") {
    $pageLink = $_SESSION['returnURL'];
  }
  else {
    $pageLink = "#";
  }

  return $pageLink;
}