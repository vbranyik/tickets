<?php
// --> Add invoice information to invoice
function add_invoice_info($incno,$invno,$finished)
{
  // --> Connect to database
  $conn = pdo_connect();

  // --> Set update query to change incident status incident
  $query = "UPDATE incidents SET invno=".$invno.",finished=".$finished." WHERE incno=".$incno;

  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }
}

// --> Close (but not invoice and incident
function close_incident($incno,$type)
{
  // --> Connect to database
  $conn = pdo_connect();

  // --> Set update query to change incident status incident
  $query = "UPDATE incidents SET finished=".$type." WHERE incno=".$incno;

  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }
}

// --> Get an incident
function get_incident($incno)
{
  // --> Connect to database
  $conn = pdo_connect();

  // --> Set query to get information
  $query = "SELECT * FROM incidents WHERE incno=".$incno;

  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  return $result;
}


// --> Create an incident
function mod_incident($incinfo,$type)
{
    // --> Connect to database
    $conn = pdo_connect();
    
    // --> Set query to insert or modify record
    if ( $type == "ADD" ) { $query = "INSERT INTO "; } else { $query = "UPDATE "; }
    
    // --> Check for type of record being created
    if ($incinfo['etype']=='std') {
      // --> Set data for query for incident
      $query .= "incidents SET " .
        "incdate='" . $incinfo['txt_year_inc']."-".$incinfo['txt_month_inc']."-".$incinfo['txt_day_inc'] . "'," .
        "reqby='" . $incinfo['reqby'] . "'," .
        "rate='" . number_format((int)$incinfo['hourlyrate'],2) . "'," .
        "custid='" . $incinfo['custid'] . "'," .
        "location='" . $incinfo['location'] . "'," .
        "equipment='" . $incinfo['equipment'] . "'," .
        "phone='" . $incinfo['phone'] . "'," .
        "issue='" . $incinfo['issue'] . "'";
    
      // --> If 'MOD' was passed
      if (strtoupper($type) == "MOD") {
        $query .= " WHERE incno=" . $incinfo['incno'];
      }
  } 
  else {
    // --> This is for a recuring (autobill) incident
    $data = [
      'custid' => $incinfo['custid'],
      'incdate' => $incinfo['txt_year_inc']."-".$incinfo['txt_month_inc']."-".$incinfo['txt_day_inc'],
      'reqby' => $incinfo['reqby'],
      'equipment' => $incinfo['equipment'],
      'location' => $incinfo['location'],
      'phone' => $incinfo['phone'],
      'issue' => $incinfo['issue'],
      'autoid' => $incinfo['autoid'],
    ];

    $query .= "incidents SET ".
      "custid=:custid," .
      "incdate=:incdate," .
      "reqby=:reqby," .
      "equipment=:equipment," .
      "location=:location," .
      "phone=:phone,".
      "issue=:issue";

    // --> If this is record being modified
    if ( $type == "MOD" ) { 
      $query .= " WHERE autoid=:autoid"; 
    }
  }
 
  // --> Run query to INSERT/MODIFY incident
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  // --> Return result
  return $conn->lastInsertId();
}

// --> Update the summary
function update_summary($incno,$summary)
{
    // --> Connect to database
    $conn = pdo_connect();
    
    // --> Set query
    $data = array(
      'incno' => $incno,
      'summary' => $summary,
    );
    
    $query = "UPDATE incidents SET summary=:summary WHERE incno=incno";

  try {
    $result = $conn->prepare($query);
    $result->execute($result);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

}