<?php

/* 
 * Author      : V. M. Branyik
 * Description : 
 * Created     : Jan 25, 2020
 * Last Edit   : 
 */

function getPayments($year) {
  // --> Connect to Database
  $conn = pdo_connect();

  // --> Query to get the payment for the year
  $query = "SELECT p.pdate AS pdate, ";
  $query .= "c.cname AS customer, ";
  $query .= "p.amount AS amount ";
  $query .= "FROM payments AS p ";
  $query .= "LEFT JOIN invoices AS i ON p.invno = i.invno ";
  $query .= "LEFT JOIN customers AS c ON c.custid = i.custid ";
  $query .= "WHERE p.pdate BETWEEN CAST('" . $year . "-01-01' AS DATE) AND CAST('" . $year . "-12-31' AS DATE) ";
  $query .= "ORDER BY p.pdate ASC";

  // --> Run the query for CUSTOMER LIST
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  // --> Get return
  $payments = array();
  while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $payments[] = [
      'customer' => $row['customer'],
      'amount' => $row['amount'],
      'pdate' => $row['pdate'],
    ];
  }
  
  return $payments;
}