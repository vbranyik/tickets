/**
 * Created on: June 10, 2020
 * Author: Vilmos Branyik
 * Purpose: Ajax call to read information from DB
 */


/**
 * 
 * @param {string} runScript : PHP file to run
 * @param {function} callback : called as result
 * @returns {function}
 */
function ajaxCall(runScript, callback) {
var response;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {

      // --> Read the result into a defined array
      callback(this.responseText);
    };
  };
  // --> Call the php code to return data
  xmlhttp.open("GET", runScript, true);
  xmlhttp.send();
}

function clearSavedStatus() {
var statusMessage = document.getElementById("statusMessage");

    if(statusMessage.innerHTML.search("saved")) {
       statusMessage.innerHTML = ""; 
    }
}

/**
 * Update the incident information in the form.
 * @param {type} response
 * @returns {nothing}
 */
function setForm(response) {
var incident = JSON.parse(response);
var input = document.createElement("input");
  
  // --> Update information in the form
  document.getElementById('incval').value = incident[0].incno;
  document.getElementById('incIssue').innerHTML = incident[0].issue;
  document.getElementById('billRate').value = incident[0].rate;
}

/**
 * Gets the incident data
 * @param {integer} incno
 * @returns {JSON}
 */
function getIncident(incno) {
    ajaxCall("/code/getincidents.php?incno=incno", setForm);
}

/**
 * Function to poulate the Select statement with current open incidents
 * @param {JSON} response
 * @returns none
 */
function setSelect(response) {
console.log(response)
var addToSelect = document.getElementById("incno");
var incidents = JSON.parse(response);  
var i;
var option = document.createElement("option");

    // --> Loop through and unpack response
    for(i in incidents) {
        var option = document.createElement("option");
        option.text = "[" + incidents[i].incno + "] " + incidents[i].title;
        option.value = incidents[i].incno;
        addToSelect.add(option);
    };
    
}

function writeComplete(result) {
var statusMessage = document.getElementById("statusMessage");

    clearForm();
    resultMsg = JSON.parse(result);
    statusMessage.innerHTML = "Time saved to incident " + resultMsg.incno;
    
}

function writeTime() {
var emptyKey;
var incnoElement = document.getElementById("incno");    
var incdateElement = document.getElementById("workDate");    
var incstartElement = document.getElementById("workStart");    
var incendElement = document.getElementById("workEnd");    
var inctimeElement = document.getElementById("billTime");    
var incrateElement = document.getElementById("billRate");    
var incworkElement = document.getElementById("workInput");  
var statusMessage = document.getElementById("statusMessage");
    
    // --> Get values
    var incno = incnoElement.options[incnoElement.selectedIndex].value;
    var incdate = incdateElement.value;
    var incstart = incstartElement.value;
    var incend = incendElement.value;
    var units = inctimeElement.value;
    var rate = incrateElement.value;
    var work = incworkElement.value;
    
    // --> Set the json object to send to the PHP script that does the work
    objEntry = { "incno":incno, "incdate":incdate, "incstart":incstart, "incend":incend, "units":units, "rate":rate, "work":work };
    objSend = JSON.stringify(objEntry);
    
    // --> Interate through values and stop submit if an empty vale is found
    statusMessage.innerHTML = "";
    for (const [key, value] of Object.entries(objEntry)) {
        if(value === "none" || value.trim() === "") {
            switch(key) {
                case "incno":
                    emptyKey = "You must select an incident.";
                    break;
                case "incdate":
                    emptyKey = "The Start date is empty or incomplete.";
                    break;
                case "incstart":
                    emptyKey = "The Start time is empty or incomplete.";
                    break;
                case "incend":
                    emptyKey = "The End time is empty or incomplete.";
                    break;
                case "units":
                    emptyKey = "The Time field is empty or incomplete.";
                    break;
                case "rate":
                    emptyKey = "The Rate field is empty or incomplete.";
                    break;
                case "work":
                    emptyKey = "The Work Performed field is empty or incomplete.";
                    break;
            }

            statusMessage.innerHTML = emptyKey;
            return;
        }  
    }
    
    // --> Verification is complete, write the record
    ajaxCall("/code/writetime.php?xgyhb=" + objSend, writeComplete);
}

function clearForm() {
    document.getElementById("incno").selectedIndex = 0;    
    document.getElementById("workDate").value = "";    
    document.getElementById("workStart").value = "";    
    document.getElementById("workEnd").value = "";    
    document.getElementById("billTime").value = "";    
    document.getElementById("billRate").value = "";    
    document.getElementById("workInput").value = "";  
}