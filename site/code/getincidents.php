<?php

/* 
 * Author      : V. M. Branyik
 * Description : Makes DB call to return open incidents in JSON format
 * Created     : Jun 10, 2020
 * Last Edit   : 
 */

session_start();

// --> Set paths
require_once dirname(__DIR__,2) . "/settings.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
    check_auth(0);
}

// --> Array for processing
$return = array();

// --> Default query
$queryType = "all";

// --> Database connection
$conn = pdo_connect();

// --> Check for type of query to run
if (isset($_GET['incno'])) {
  $queryType = "incident";
  $incno = $_GET['incno'];
}

// --> Set query based on request
switch($queryType) {
  case "incident" :
    $query = "SELECT ".
      "incidents.incno AS incno, ".
      "incidents.issue AS issue, ".
      "incidents.rate AS rate ".
      "FROM incidents ".
      "LEFT JOIN worklog ON incidents.incno = worklog.incno ".
      "WHERE incidents.incno=" . $incno;
    break;
  default:
    // --> All open incidents
    $query = "SELECT ".
      "incno, ".
      "title ".
      "FROM incidents ".
      "WHERE finished = 0 ".
      "ORDER BY incdate DESC,incno DESC";
}

// --> Run the query
try {
  $result = $conn->query($query);
}
catch (PDOException $e) {
  echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
}

// --> Create array from result
while ($i = $result->fetch(PDO::FETCH_ASSOC)) {
  if ($queryType == "incident") {
    $return[] = array(
      "incno" => $i['incno'],
      "issue" => $i['issue'],
      "rate" => $i['rate'],
    );
  }
  else {
    $return[] = array(
      "incno" => $i['incno'],
      "title" => $i['title'],
    );
  }
}

// --> Return the result in JSON format
echo json_encode($return);