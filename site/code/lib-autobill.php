<?php

function set_next_bill($autoid,$nextbill)
{
    // --> Connect to database
    $conn = pdo_connect();

    // --> Set the query
    $query = "UPDATE autobill SET nextbill='".date("Y-m-d",$nextbill)."' WHERE autoid=".$autoid;
    
    // --> Run query to get INCIDENT information
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }

}

