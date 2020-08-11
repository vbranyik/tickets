<?php
/* 
 * This is the library file for setauto.php
 */

function get_autobill_recs () {
  $retvar = array();
    
  // --> Connect to database
  $conn = pdo_connect();
    
  // --> Set query
  $query = "SELECT ";
  $query .= "a.autoid AS autoid, ";
  $query .= "a.freq AS freq, ";
  $query .= "c.terms AS terms,  ";
  $query .= "c.cname AS cname,  ";
  $query .= "a.issue AS issue,  ";
  $query .= "a.workperf AS work,  ";
  $query .= "a.units AS units,  ";
  $query .= "a.rate AS rate,  ";
  $query .= "a.nextbill AS nextbill,  ";
  $query .= "a.expire AS expire  ";
  $query .= "FROM autobill AS a ";
  $query .= "LEFT JOIN customers AS c ON a.custid=c.custid ";
  $query .= "ORDER BY a.nextbill DESC,c.cname ";
    
  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  // --> Return as array
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $retvar[] = $row;
  }

  return $retvar;
}

function get_record($autoid) {
  // --> Connect to database
  $conn = pdo_connect();
    
  // --> Set query
  $query = "a.autoid AS autoid, ";
  $query = "SELECT ";
  $query .= "a.freq AS freq, ";
  $query .= "c.cname AS cname,  ";
  $query .= "a.reqby AS reqby,  ";
  $query .= "a.equipment AS equipment,  ";
  $query .= "a.issue AS issue,  ";
  $query .= "a.location AS location,  ";
  $query .= "a.phone AS phone,  ";
  $query .= "a.workperf AS workperf,  ";
  $query .= "a.units AS units,  ";
  $query .= "a.rate AS rate,  ";
  $query .= "a.nextbill AS nextbill,  ";
  $query .= "a.expire AS expire  ";
  $query .= "FROM autobill AS a ";
  $query .= "LEFT JOIN customers AS c ON a.custid=c.custid ";
  $query .= "WHERE autoid=".$autoid;

  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }
    
  // --> Return record
  return $result->fetch(PDO::FETCH_ASSOC);
}

function save_record($posted,$type) {
  // --> Connect to database
  $conn = pdo_connect();
    
  // --> Set save query

  $query = strtoupper($type)." autobill SET ";
  $query .= "freq='".$posted['freq']."',";
  $query .= "reqby='".$posted['reqby']."',";
  $query .= "equipment='".$posted['equipment']."',";
  $query .= "issue='".$posted['issue']."',";
  $query .= "location='".$posted['location']."',";
  $query .= "phone='".$posted['phone']."',";
  $query .= "workperf='".$posted['workperf']."',";
  $query .= "units='".$posted['units']."',";
  $query .= "rate='".$posted['rate']."',";
  $query .= "nextbill='".$posted['nextbill']."',";
  $query .= "expire='".$posted['expire']."'";
    
  // --> Add for update query
  if ( strtolower($type) == "update" ) {
    $query .= " WHERE autoid=".$posted['autoid'];
  }

  // --> Run query to get INCIDENT information
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  // --> Get the id of the record
  // $autoid = $db->insert_id;
  $autoid = $posted['autoid'];

  // --> Return the account ID (for now)
  return $autoid;
    
}