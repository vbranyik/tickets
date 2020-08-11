<?php

// --> Log into system
function sign_in($user,$pass) {
// --> This function will return the following codes:
// --> NOTFOUND - The user does not exist
// --> FAILED - Invalid password supplied by user
// --> LOCKED - Account has exceeded login attempts
// --> VALID - A valid user authentication

	// --> Database connection
  $conn = pdo_connect();
  
  // Set up query to find user
  $query = "SELECT * FROM users WHERE custid='".$user."'";
 
  // Run the query
  try {
    $result = $conn->query($query);
  }
  catch (PDOException $e) {
    echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
  }

  // Get the result
  $row =  $result->fetch();

	if ( !$row ) {
    // The user was not found
		$status_flag = "NOTFOUND";
	}
	else {  // The user was found, check the password
    // --> Transfer record columns for processing
    $usrid = $row['usrno'];
    $fname = $row['fname'];
    $email = $row['email'];
    $failed = $row['failed'];
    $lockend = $row['lockend'];
    $locked = $row['locked'];

    // --> Check for lock on the account
    if ( $locked == "Y" && ( time() < strtotime($lockend) ) ) {
        $status_flag = "INLOCK";
    }
    else {
      // Check password
      if ( md5($pass) != $row['passwd'] ) {
        // --> The password was incorrect
        $status_flag = "FAILED";
      }
      else { // --> The password was correct

        // --> Set flag for regular login
        $status_flag = "VALID";

        // Set Session Variables for user
        $_SESSION['valid'] = "Y";
        $_SESSION['user'] = trim(htmlspecialchars($row['fname']))."&nbsp;".trim(htmlspecialchars($row['lname']));
        $_SESSION['usrid'] = $row['custid'];

        // --> Set a query to rest account for good login
        $query = "UPDATE users SET failed=0,locked='N',lockend='2007-09-27 00:00:00' WHERE usrno=".$usrid;
      }
    }
  }
    
    // --> Check status flag to see if the login faile
    if ( $status_flag == "FAILED" ) {
      // --> Check to see account is already locked
      if ( $locked == "Y" ) {
        // --> The account is in "Locked" status check to see if beyond time out
        if ( time() > strtotime($lockend) ) {
          // --> Reset the failed to 1 (because this attempt failed)
          $query = "UPDATE users SET failed=1,locked='N',lockend='".gmdate("Y-m-d H:i:s")."' WHERE usrno=".$usrid;
        }
        else {
          // --> Lock stays in place
          $status_flag = "INLOCK";
        }
      }
        else { // --> The account is NOT locked yet
          // --> Check to see if the last expire time is greater than 15 minutes ago.
             
          // --> If so reset lockend to +15 minutes from now and failed to 1 (because this one failed)

          if ( time() > strtotime($lockend."+15 minutes") ) {
            // --> Query to reset the counter to 1 and window for 6 login attempts to 15 minutes from now
            // --> This login has 5 remaining failed attempts left in the next 15 minutes
            $query = "UPDATE users SET failed=1,lockend='".gmdate("Y-m-d H:i:s",strtotime("+15 minute"))."' WHERE usrno=".$usrid;
          }
          else {
            if ( $failed < 5 ) {
              // --> Increment failed counter
              $failed++;

              // --> Add 1 to the counter
              $query = "UPDATE users SET failed=".$failed." WHERE usrno=".$usrid;
            }
            else {
              // --> Lock the account for the next 15 minutes
              $query = "UPDATE users SET failed=6,locked='Y',lockend='".gmdate("Y-m-d H:i:s",strtotime("+15 minute"))."' WHERE usrno=".$usrid;

              // --> Set status flag to LOCKED
              $status_flag = "LOCKED";
            }
          }

        }
    }
    
    // --> Update user account and return result of function
    // --> As log as the status is not set to "NOTFOUND"
    if ( $status_flag != "NOTFOUND" && $status_flag != "INLOCK" ) {
      // Run the query
      try {
        $result = $conn->query($query);
      }
      catch (PDOException $e) {
        echo 'Connection failed:<br />Query:' . $query . "<br />" . $e->getMessage();
      }
    }

    // --> Check to see if LOCKED. If so send notification email
    if ( $status_flag == "LOCKED" ) { notify_user($email,strtotime($lockend)+date("Z"),"LOCKED"); }

    // --> Send back the result
    return $status_flag;
    
}

// --> Notify user that the account is locked
function notify_user($email,$data,$type)
{
    // Set the email header
    $headers = "From: Tickets <auto@bc-cs.info>". "\r\n" .
    "Reply-To: noreply@bc-cs.info". "\r\n" .
    "X-Mailer: PHP/" . phpversion();
    
    // --> Set subject line based on type
    switch ($type)
    {
        case "ECHANGE":
            // --> User has changed address - notify original address
            $subject = "Your AvailsBot email address was changed";
            
            // --> Set the message
            $message = "This is an automated message from the AvailsBot system."."\r\n".
            "\r\n".
            "The email address attached to your AvailsBot account was CHANGED."."\r\n".
            "\r\n".
            "It was changed to this address: ".$data.".\r\n".
            "\r\n".
            "If you did not request this your account information may be compromised."."\r\n".
            "\r\n".
            "It is highly recommended you change your password immediately. To do so log into your account and change your password. That is done on the accounts menu."."\r\n".
            "\r\n".
            "In the event you require further assistance with this please contact AvailsBot support at support@availsbot.com".".\r\n".
            "\r\n".
            "Thank you for using the AvailsBot system!";
            break;

        case "LOCKED":
            // --> User is locked
            $subject = "Your Ticket account is LOCKED";
            
            // --> Set the message
            $message = "This is an automated message from the Ticketing system"."\r\n".
            "\r\n".
            "Your account status is LOCKED"."\r\n".
            "\r\n".
            "The LOCKED status will expire at ".date("h:i a",$data)." on ".date("M j, Y",$data)."\r\n".
            "\r\n".
            "Your account was LOCKED because there were 6 incorrect login attempts in a 15 minute period. Your account will be unaccessable until the LOCK period has expired; even with the correct password. This is a security feature that is done by design."."\r\n".
            "\r\n".
            "This could be the result of an attempt by someone other than yourself to access your account by guessing your password. It could also be because you have forgotten your password."."\r\n".
            "\r\n".
            "In the event these attempts came from you please wait until the LOCK period has expired. Then go the the AvailsBot login page and click on the Forgot Password link."."\r\n".
            "\r\n".
            "In the event these attempts did not come from you it is highly recommended you change your password immediately. To do so once the LOCK period has expired log into your account and change your password. That is done on the accounts menu"."\r\n".
            "\r\n".
            "in the event you require further assistance with this please contact AvailsBot support at support@availsbot.com"."\r\n".
            "\r\n".
            "Thank you for using the Ticketing system!";
            break;

        case "NEMAIL":
            $subject = "Your AvailsBot email address was changed";
            
            // --> Set the message
            $message = "This is an automated message from the AvailsBot system."."\r\n".
            "\r\n".
            "The email address attached to your AvailsBot account has been CHANGED to this address ".$email.".\r\n".
            "\r\n".
            "In the event you did not request this change or you do not have an AvailsBot account please contact AvailsBot support at support@availsbot.com for further assistance."."\r\n".
            "\r\n".
            "Thank you for using the AvailsBot system!";
            break;

        case "RESET":
            // --> Sending password
            $subject = "The AvailsBot information you requested";

            // --> Set the message
            $message = "This is an automated message from the AvailsBot system"."\r\n".
            "\r\n".
            "Your link is http://user.availsbot.com/reset.php?code=".$data."\r\n".
            "\r\n".
            "The above link is valid for 30 minutes from the time issued."."\r\n".
            "\r\n".
            "If you did not request this your account information may be compromised."."\r\n".
            "\r\n".
            "It is highly recommended you change your password immediately. To do so log into your account and change your password. That is done on the accounts menu"."\r\n".
            "\r\n".
            "in the event you require further assistance with this please contact AvailsBot support at support@availsbot.com"."\r\n".
            "\r\n".
            "Thank you for using the AvailsBot system!";
            break;
    }
    
    // --> Send the message
    mail($email,$subject,$message,$headers,"-f auto@availsbot.com");
}

?>