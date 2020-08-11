<?php
function check_date($mo,$da,$yr)
{
    if ( checkdate(intval($mo),intval($da),intval($yr)) == false )
    {
        // --> Find the last day of the month requested
        $lastday = date("t",mktime(0,0,0,intval($mo),1,intval($yr)));
        
        // --> Set the new date one day ahead of the last day of that month
        $newdate = date("Y-m-d",mktime(0,0,0,intval($mo),intval(($lastday + 1)),intval($yr)));
        
        // --> Set alert code
        $_SESSION['alert_message'] = $_SESSION['alert_message'] + 10;
        
        // --> Return the date
        return $newdate;
    }
    else
    {
        // --> Return the sent date
        return $yr."-".$mo."-".$da;
    }
}

function enter_date($sendtime,$addon)
{
// --> Break the date passed up
$procmo = date("n",$sendtime);
$procday = date("j",$sendtime);
$procyear = date("Y",$sendtime);

print "<select size=\"1\" name=\"txt_month".$addon."\">";
	// Month												
	for ($z=1; $z <= 12; $z = $z + 1 )
	{
		$checkval = intval($procmo);
		
		if ( $z == $checkval ) 
		{
			print("<option selected>".sprintf("%02d",$z)."</option>"); 
		} 
		else 
		{
			print("<option>".sprintf("%02d",$z)."</option>"); 
		}
	}
print "</select>";
print "<select size=\"1\" name=\"txt_day".$addon."\">";
	// Day											
	for ($z=1; $z <= 31; $z = $z + 1 )
	{
		$checkval = intval($procday);
		
		if ( $z == $checkval ) 
		{
			print("<option selected>".sprintf("%02d",$z)."</option>"); 
		} 
		else 
		{
			print("<option>".sprintf("%02d",$z)."</option>"); 
		}
	}
print "</select>";
print "<select size=\"1\" name=\"txt_year".$addon."\">";
        
        // --> Check to see if this is a prompt that will allow "history"
        // --> if ( $addon == "_goto" ) { $z = 2011; } else { $z = date("Y"); }
        $z = date("Y",$sendtime) - 2;

        // Year
	for ($z; $z <= date("Y")+2; $z = $z + 1 )
	{
		$checkval = intval($procyear);
		
		if ( $z == $checkval ) 
		{
			print("<option selected>".sprintf("%02d",$z)."</option>"); 
		} 
		else 
		{
			print("<option>".sprintf("%02d",$z)."</option>"); 
		}
	}
print "</select>";
}

function enter_time($initial_date,$addon)
{
			print("<select size=\"1\" name=\"txt_hour\"".$addon.">");
			// hour												
			for ($x=1; $x <= 12; $x = $x + 1 )
			{
				$checkval = intval(date("h",$initial_date));
				
				if ( $x == $checkval ) 
				{
					print("<option selected>".sprintf("%02d",$x)."</option>"); 
				} 
				else 
				{
					print("<option>".sprintf("%02d",$x)."</option>"); 
				}
			}
			print("</select>");
			print("<select size=\"1\" name=\"txt_minute".$addon."\">");
			// minute												
			for ($x=0; $x < 60; $x = $x + 15 )
			{
				$checkval = intval(date("i",$initial_date));
				
				if ( $x == $checkval ) 
				{
					print("<option selected>".sprintf("%02d",$x)."</option>"); 
				} 
				else 
				{
					print("<option>".sprintf("%02d",$x)."</option>"); 
				}
			}
			print("</select>");
			print("<select size=\"1\" name=\"txt_ampm".$addon."\">");
			// AM-PM												
				if ( date("A",$initial_date) == "PM" ) 
				{
					print("<option>AM</option>"); 
					print("<option selected>PM</option>"); 
				} 
				else 
				{
					print("<option selected>AM</option>"); 
					print("<option>PM</option>"); 
				}
			print("</select>");

}		

function get_zone($zone,$type)
{
// --> Type codes
// --> 0 : Name
// --> 1 : Offset in hours
// --> 2 : Offset in seconds
    
    // --> Search zones
    switch ( $zone )
    {
        case "Pacific/Honolulu":
            $tzdata[0] = "US/Hawaii";
            $tzdata[1] = -10;
            break;
        case "America/Anchorage":
            $tzdata[0] = "US/Alasks";
            $tzdata[1] = -9;
            break;
        case "America/Los_Angeles":
            $tzdata[0] = "US/Pacific";
            $tzdata[1] = -8;
            break;
        case "America/Phoenix":
            $tzdata[0] = "US/Arizona";
            $tzdata[1] = -7;
            break;
        case "America/Denver":
            $tzdata[0] = "US/Mountain";
            $tzdata[1] = -7;
            break;
        case "America/Chicago":
            $tzdata[0] = "US/Central";
            $tzdata[1] = -6;
            break;
        case "America/New_York":
            $tzdata[0] = "US/Eastern";
            $tzdata[1] = -7;
            break;
    }

    // --> Set return based on type
    switch ( $type )
    {
        case 2:
            $tz = (($tzdata[1]*60)*60);
            break;
        case 1:
            $tz = $tzdata[1];
            break;
        default:
            $tz = $tzdata[0];
    }

    // --> Return the requested data
    return $tz;
}


// --> Get the timezone to process based on GMT
function return_zone_query($hour)
{
    // --> Set defaults
    $tzadd = 0;         // --> DST add
    $query = "";        // --> Blank query
    
    // --> Convert current date to number
    $dst_check = strtotime(gmdate("Y-m-d H:i:s"));
    
    // --> Check to see if this is DST
    switch ( $dst_check )
    {
        case $dst_check > strtotime(gmdate("2013-03-10 08:00:00")) && $dst_check < strtotime(gmdate("2013-11-03 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2014-03-09 08:00:00")) && $dst_check < strtotime(gmdate("2014-11-02 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2015-03-08 08:00:00")) && $dst_check < strtotime(gmdate("2015-11-01 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2016-03-13 08:00:00")) && $dst_check < strtotime(gmdate("2016-11-06 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2017-03-12 08:00:00")) && $dst_check < strtotime(gmdate("2017-11-05 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2018-03-11 08:00:00")) && $dst_check < strtotime(gmdate("2018-11-04 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2019-03-10 08:00:00")) && $dst_check < strtotime(gmdate("2019-11-03 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2020-03-08 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-01 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2021-03-14 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-07 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2022-03-13 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-06 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2023-03-12 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-05 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2024-03-10 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-03 08:00:00")):
            $tzadd = -1;
            break;
        case $dst_check > strtotime(gmdate("2025-03-09 08:00:00")) && $dst_check < strtotime(gmdate("2020-11-02 08:00:00")):
            $tzadd = -1;
            break;
        default:
            $query = "NOTFOUND";
    }
    
    // --> If this is an hour for to run for
    if ( $query != "NOTFOUND" )
    {
        // --> Check the hour
        switch ($hour)
        {
            case (5 + $tzadd):
                $zone = "America/New_York";
                break;
            case (6 + $tzadd):
                $zone = "America/Chicago";
                break;
            case (7 + $tzadd):
                $zone = "America/Mountain";
                break;
            case (8 + $tzadd):
                $zone = "America/Los_Angeles";
                break;
            case (9 + $tzadd):
                $zone = "America/Anchorage";
                break;
            case (10 + $tzadd):
                $zone = "America/Honolulu";
                break;
        }
        
        // --> Create query add for AZ
        if ( $tzadd == 0 && $hour == 7 ) { $az = " OR tzone = America/Denver"; }
    
        // --> Create query
        $query  = $zone.$az;
    }
    
    // --> Return offset name
    return $query;
}

// --> Timezone set
function set_tz($zone)
{
    // --> Check if zone is blank, if so set to Chicago
    if ( $zone == "" ) { $zone = "US/Central"; }

    // --> Set zone array
    $tzdata[0][0] = "US/Hawaii";
    $tzdata[0][1] = "Pacific/Honolulu";

    $tzdata[1][0] = "US/Alaska";
    $tzdata[1][1] = "America/Anchorage";

    $tzdata[2][0] = "US/Pacific";
    $tzdata[2][1] = "America/Los_Angeles";

    $tzdata[3][0] = "US/Arizona";
    $tzdata[3][1] = "America/Phoenix";

    $tzdata[4][0] = "US/Mountain";
    $tzdata[4][1] = "America/Denver";

    $tzdata[5][0] = "US/Central";
    $tzdata[5][1] = "America/Chicago";

    $tzdata[6][0] = "US/Eastern";
    $tzdata[6][1] = "America/New_York";

    // --> Start select box
    print "<select name=\"tzone\">";
    
    // --> Loop through thezones for display
    for ($z=0; $z<=count($tzdata)-1; $z++)
    {
        // --> Check to see if the zone being printed matches the zone passed
        if ( $tzdata[$z][0] == $zone ) { $zone_selected = " SELECTED"; } else { $zone_selected = ""; }
        
        print "<option value=\"" . $tzdata[$z][1] . "\"".$zone_selected.">" . $tzdata[$z][0] . "</option>";
    }
    
    // --> Close select box
    print "</select>";
}

?>