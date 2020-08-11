<?php

function mod_worklog($item,$type)
{
    // --> Set hour of the day based on AM/PM passd
    if ( $item['txt_ampm_in'] == "AM"  ) {
        if ( $item['txt_hour_in'] == 12 ) { $item['txt_hour_in'] = 0; }
    }

    // --> Set hour of the day based on AM/PM passd
    if ( $item['txt_ampm_out'] == "AM"  ) {
        if ( $item['txt_hour_out'] == 12 ) { $item['txt_hour_out'] = 0; }
    }

    // --> Connect to database
    global $db;

    // --> Set type of query
    if ( $type == "ADD" ) { $query = "INSERT into "; } else { $query = "UPDATE "; }
    
    // --> Set the body of the query
    $query .= "worklog SET ".
            "incno=".$item['incno'].",".
            "workdate='".$item['txt_year_log']."-".$item['txt_month_log']."-".$item['txt_day_log']."',".
            "workperf='".mysqli_real_escape_string($db,$item['workperf'])."',".
            "timein='".$item['txt_hour_in'].$item['txt_minute_in']."00',".
            "timeout='".$item['txt_hour_out'].$item['txt_minute_out']."00',".
            "units=".$item['units'].",".
            "rate=".$item['rate'];
    
    // --> Add where clause if modifying
    if ( $type == "MOD" ) { $query .= "WHERE logid=".$item['logid']; }
    
    // --> Run query to get INCIDENT information
    try {
      $result = $conn->query($query);
    }
    catch (PDOException $e) {
      echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
    }
}

