// Global Variables

var CHECK_SESSION_TIMER = 2000;
var CHECK_MESSAGE_TIMER = 1000;
var MAIN_ANON_URL = "anon.php";

// Calls the proper update function depending on bookmark (set by session)
function init() {
	document.getElementById("uploadMessage").innerHTML = uploadMessage;
	update(bookmark);
}

function update(pageId) {
	
	var func = pageId + "Update";
	window[func]();
}

function loginUpdate() {
	// No updates needed
}

function queueUpdate() {
	get_queue_number();
	setInterval("check_session()", CHECK_SESSION_TIMER);
}

function chatUpdate() {
	setInterval("get_message()", CHECK_MESSAGE_TIMER);
}

function exitUpdate() {
	// No updates needed
}

function sendAjaxRequest(obj, handler) {
	xhrequest = null;
	try {
		xhrequest = getXMLHttpRequest();
	} catch(error) {
		document.write("Cannot run Ajax code using this browser");
	}
	
	var csrfg = document.getElementById("csrfg").value;
	var temp = JSON.parse(obj);
	temp.CSRFGuard = (csrfg);
	obj = JSON.stringify(temp);
	
	var strUrl = "HDcontroller.php?obj=" + obj;
	xhrequest.onreadystatechange = handler;
	xhrequest.open("GET", strUrl, true);
	xhrequest.send(null);
}

function getXMLHttpRequest() {
	var xhrequest = null;
	if(window.XMLHttpRequest) {
		try {
			xhrequest = new XMLHttpRequest();
			return xhrequest;
		} catch(exception) {
			//OK
		}
	} else {
		var IEControls = ["MSXML2.XMLHttp.5.0","MSXML2.XMLHttp.4.0","MSXML2.XMLHttp.3.0","MSXML2.XMLHttp"];
		for (var i=0; i<IEControls.length; i++) {
			try {
				xhrequest = new ActiveXObject(IEControls[i]);
				return xhrequest;
			} catch(exception) {
				
			}
		}
		throw new Error("Cannot create an XMLHttpRequest");
	}
}

// Gives input type buttons the ability to click upon the enter key being pressed
// Works only if the input field + input button has "field" and "fieldButton" naming scheme
// The current enter button configuration works for firefox and chrome
function enterButton(event, id) {
	var key = event.keyCode || event.charCode;
	if(key === 13) {
		var buttonId = id + "Button";
		document.getElementById(buttonId).click();
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setAnon","strUserId":"Rachel"}
function enter_queue() {
	// Get Data Needed for AJAX Request
	var name = document.getElementById("strUserId").value;
	var obj = JSON.stringify( {"mode":"setAnon","strUserId": name } );
	
	// Send request with given object and handler
	sendAjaxRequest(obj, enter_queue_handler);
}


function enter_queue_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			window.location.href = MAIN_ANON_URL;
		} else {
			// error handling
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"exitQueue"}
function exit_queue() {
	// Get Data Needed for AJAX Request
	var obj = JSON.stringify( {"mode":"exitQueue"} );
	
	// Send request with given object and handler
	sendAjaxRequest(obj, exit_queue_handler);
}

function exit_queue_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			window.location.href = MAIN_ANON_URL;
		} else {
			// error handling
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"num_queue"}
function get_queue_number() {
	var obj = JSON.stringify( {"mode":"num_queue"} );
	sendAjaxRequest(obj, get_queue_number_handler);
}

function get_queue_number_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var txt = "You are in position number: " + strResponse.message;
			document.getElementById("queueNumberMessage").innerHTML = txt;
			setTimeout("get_queue_number()", CHECK_SESSION_TIMER);
		} else {
			// error handling
		}
	}	
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"checkSessionAnon"}
function check_session() {
	var obj = JSON.stringify( {"mode":"checkSessionAnon"} );
	sendAjaxRequest(obj, check_session_handler);
}

function check_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			if (strResponse.message === "YES") {
				window.location.href = MAIN_ANON_URL;
			} else {
				setTimeout("check_session()", CHECK_SESSION_TIMER);
			}
		} else {
			// error handling
		}
	}	
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getMessageForAnon"}
function get_message() {
	var obj = JSON.stringify( {"mode":"getMessageForAnon"} );
	sendAjaxRequest(obj, get_message_handler);
}

function get_message_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var txt = "";
			for (var i = 0; i < strResponse.message.length; i++) {
				txt += "<p>" + strResponse.message[i] + "</p>";
			}
			document.getElementById("chatroom").innerHTML = txt;
			
			has_session_ended();
		} else {
			// error handling
		}
	}
}

function has_session_ended() {
	var obj = JSON.stringify( {"mode":"hasSessionEnded"} );
	sendAjaxRequest(obj, has_session_ended_handler);
}

function has_session_ended_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		
		// If it has ended, append "session has ended" and don't call get message
		if (strResponse.status == "OK") {
			if (strResponse.message === "Ended") {
				document.getElementById("chatroom").innerHTML += "Session Has Ended.";
			} else {
				setTimeout("get_message()", CHECK_MESSAGE_TIMER);
			}
		} else {
			// Error
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setMessageAnon", "strMessage":"new message"}
function set_message() {
	var message = document.getElementById("strMessage").value;
	var obj = JSON.stringify( {"mode":"setMessageAnon", "strMessage": message} );
	sendAjaxRequest(obj, set_message_handler);
}

function set_message_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			document.getElementById("strMessage").value = "";
			get_message();
			document.getElementById("strMessage").focus();
		} else {
			document.getElementById("sendErrorMessage").value = strResponse.message;
			document.getElementById("strMessage").focus();
		}
	}
}

function new_anon_session() {
	// Get Data Needed for AJAX Request
	var obj = JSON.stringify( {"mode":"newAnonSession"} );
	
	// Send request with given object and handler
	sendAjaxRequest(obj, new_anon_session_handler);
}

function new_anon_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			window.location.href = MAIN_ANON_URL;
		} else {
			// error handling
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"endSessionForAnon"}
function end_session() {
	var obj = JSON.stringify( {"mode":"endSessionForUser"} );
	sendAjaxRequest(obj, end_session_handler);
}

function end_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			window.location.href = MAIN_ANON_URL;
		} else {
			// Session already ended by HDuser
			window.location.href = MAIN_ANON_URL;
		}
	}
}

