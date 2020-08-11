<?php

/* 
 * Author      : V. M. Branyik
 * Description : Functions for timeentry.php page
 * Created     : Jun 14, 2020
 * Last Edit   : Jun 17, 2020
 */


function post_time($enteredTime) {
  // --> Database connection
  $conn = pdo_connect();

  // --> Set data that was received for the query
  $data = array(
    'incno' => $enteredTime['incno'],
    'workdate' => $enteredTime['workDate'],
    'timein' => $enteredTime['workStart'],
    'timeout' => $enteredTime['workEnd'],
    'workperf' => $enteredTime['workPerf'],
    'units' => $enteredTime['billTime'],
    'rate' => $enteredTime['billRate'],
  );

  // --> Set query to post
  $query = "INSERT INTO  incidents (".
    "incno, ".
    "workdate, ".
    "timein, ".
    "timeout, ".
    "workperf, ".
    "units, ".
    "rate ".
    ") VALUES (".
    ":incno, ".
    ":workdate, ".
    ":timein, ".
    ":timeout, ".
    ":workperf, ".
    ":units, ".
    ":rate)";

  // --> Run the query
  try {
    echo $query . "<br />";
    var_dump($data);
    exit;
    $result = $conn->query($query);
    $result->execute($data);
    
    // --> Return id to verify item was posted
    return $conn->lastInsertId();
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }


}