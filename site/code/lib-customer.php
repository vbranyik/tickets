<?php

// --> Get customer list
function get_cust_list($order)
{
    // --> Set order variable
    switch ($order)
    {
        case 1:
            $orderby = " ORDER BY cname DESC";
            break;
        default:
            $orderby = " ORDER BY cname ASC";
            break;
    }

    // --> Connect to Database
    $conn = pdo_connect();

    // --> Set query to get customer list
    $query = "SELECT * FROM customers".$orderby;
    
    // --> Run the query for CUSTOMER LIST
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }
    
    // --> Return result
    return $result;
}

// --> Get Customer information
function get_customer($custid)
{
    // --> Connect to Database
    $conn = pdo_connect();

    // --> Set query to get customer list
    $query = "SELECT * FROM customers WHERE custid='".$custid."'";
    
    // --> Run the query for CUSTOMER information
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }
    
    // --> Return result
    return $result;
}
