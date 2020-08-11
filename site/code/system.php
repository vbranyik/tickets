<?php

/* 
 * Author      : V. M. Branyik
 * Description : This holds functions absolutely needed to load a page
 * Created     : Dec 15, 2019
 * Last Edit   :
 */

// --> Set the system paths to load DB and libraries
function set_paths() {
  // --> Create array
  $sys_path = array();
  
  // --> Test for what this is running on
  if ( substr($_SERVER['HTTP_HOST'],-6) == ".local" ) {
      // --> Running locally
      $sys_path['code'] = "/home/matyas/vhosts/tickets/v1.1/code";
      $sys_path['fonts'] = "/home/matyas/vhosts/tickets/v1.1/fonts";
      $sys_path['classes'] = "/home/matyas/vhosts/tickets/v1.1/classes";
      $sys_path['pdf_path'] = "/home/matyas/vhosts/tickets/v1.1/classes";

      // --> Database connect profile
      require_once "/home/matyas/vhosts/tickets/v1.1/_dbconnect/tickets.dev.php";
  } else {
      // --> Running on Public server
      $sys_path['code'] = "/home4/vbranyik/webs/tickets/code";
      $sys_path['fonts'] = "/home4/vbranyik/webs/tickets/fonts";
      $sys_path['classes'] = "/home4/vbranyik/webs/tickets/classes";
      $sys_path['pdf_path'] = "/home4/vbranyik/tickets";

      // --> Database connect profile
      require_once "/home4/vbranyik/dbinfo/tickets.php";
  }

  // --> Database connection
  global $db;
  
  // --> Return paths
  return $sys_path;
}

