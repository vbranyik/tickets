<?PHP
// --> Author       : Hired Gun Coding : V. M. Branyik
// --> Description  : Login screen
// --> Last Edit    : 11/09/14
// --> Upgrade ready:

session_start();

// --> Load paths based on server and then load libraries 
require_once dirname(__DIR__) . "/settings.php";

// Check to see if page authentication is needed
if (strtolower($auth) == "yes") {
  check_auth(0);
}

// --> Required libraries
require_once $sys_path['code'] . "/lib-timeentry.php.inc";

// --> If the POST array is set save data
if (isset($_POST) && count($_POST) > 0) {
  $newLogItem = post_time($_POST);
  echo $newLogItem;
  exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
    <meta name="author" content="Hired Gun Coding" />
    
    <!-- Style sheets -->
    <link rel="stylesheet" href="/styles/shared.css" />
    <link rel="stylesheet" href="/styles/responsive.css" />
    <link rel="stylesheet" href="/styles/time.css" />
	
    <title>Branyik Consulting | Ticket System | Login</title>
</head>
<body>
  <div id="main-container">
    <h1>Time Entry</h1>
    <div id="getInc" class="padInput">
      <div class="incnum">
        <label for="incno">Incident:</label>
        <select name="incno" id="incno" onchange="clearSavedStatus()">
          <option value="none">-- Select --</option>
        </select>
      </div>
<!--  Display number of hour so far
      <div class="previousHours"></div>
-->
    </div>
    <div id="submitTime">
      <div id="incIssue" class="padInput"></div>
      <div class="padInput workDate">Date: <input type="date" id="workDate" name="workDate" /></div>
      <div id="workTime" class="padInput">
        <div class="startTime">Start time: <input type="time" step="900" id="workStart" name="workStart" /></div>
        <div class="endTime">End time: <input type="time" step="900" id="workEnd" name="workEnd" /></div>
      </div>
      <div id="calcTime" class="padInput">
        <div class="billTime">Time: <input type="text" size="5" id="billTime" name="billTime" /></div>
        <div class="billRate">Rate: <input type="text" size="5" id="billRate" name="billRate" /></div>
      </div>
      <div id="workPerf" class="padInput">
        <h2>Work Performed</h2>
        <textarea id="workInput" class="padInput" name="workPerf"></textarea>
      </div>
      <div id="submit-line" style='overflow: hidden;'>
          <div id="submit-time" class="status-line"><input type="submit" value="Submit Time" onclick="writeTime()" /></div>
          <div id="statusMessage" class="status-line"></div>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="js/time.js"></script>
  <script type="text/javascript">
//    const selectElement = document.querySelector('#incno');
//
//    selectElement.addEventListener('change', (event) => {
//      // setForm(event.target.value);
//      ajaxCall("/code/getincidents.php?incno=" + event.target.value, setForm);
//    });


  document.addEventListener('DOMContentLoaded', function() {
      ajaxCall("/code/getincidents.php", setSelect);
    }, false);
  </script>
</body>
</html>