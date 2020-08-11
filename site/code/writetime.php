<?php

/* 
 * Author      : V. M. Branyik
 * Description : Makes DB call inset time entry
 * Created     : Aug 9, 2020
 * Last Edit   : 
 */

session_start();

// --> Set paths
require_once dirname(__DIR__,2) . "/settings.php";

// Check to see if logged in, ignore if on dev server
if ( substr($_SERVER['HTTP_HOST'],-6) != ".local" ) {
    check_auth(0);
}

// --> Default query
$queryType = "all";

// --> Database connection
$conn = pdo_connect();

// --> Check for type of query to run
if (isset($_GET['xgyhb'])) {
  $workLog = json_decode($_GET["xgyhb"], false);
}
else {
  $message = [
    'status' => 'EMPTY',
    'incno' => '',
  ];

  $return = json_encode($message);
  echo $return;
  exit;
}

$query = "INSERT INTO worklog ";
$query .= "SET ";
$query .= "incno='" . $workLog->incno . "', ";
$query .= "workdate='" . $workLog->incdate . "', ";
$query .= "timein='" . $workLog->incstart . "', ";
$query .= "timeout='" . $workLog->incend . "', ";
$query .= "units='" . $workLog->units . "', ";
$query .= "rate='" . $workLog->rate . "', ";
$query .= "workperf='" . $workLog->work . "'";


// --> Run the query
try {
  $result = $conn->query($query);
  $message = [
    'status' => 'SUCCESS',
    'incno' => $workLog->incno,
  ];

  $return = json_encode($message);
  echo $return;
}
catch (PDOException $e) {
  echo 'PDOEXCEPTION';
}
