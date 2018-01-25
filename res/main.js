
// STATIC FINAL VARIABLES
var CHECK_SESSION_TIMER = 3000;
var CHECK_MESSAGE_TIMER = 1000;
var NAV = "nav";
var ACTIVE = "active";

function init() {
	showPage('homepage');
	document.getElementById("homepageWelcome").innerHTML += userId;	
	get_registered_users();
}

function logout() {
	// need to add ajax function to unset session
	var obj = JSON.stringify( {"mode":"logoutHDUser"} );
	sendAjaxRequest(obj, logout_handler);
}

function logout_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		
		if (strResponse.status == "OK") {
			window.location = "login.php";
		} else {
			// Error
		}
	}
}

function showPage(pageId) {
	var pages = document.getElementsByClassName("Page");
	for (var i = 0; i < pages.length; i++) {
		//alert(pages[i].id == pageId );
		if (pages[i].id == pageId) {
			pages[i].style.display="inline";
			showNav(pageId);
			update(pageId);
		} else {
			pages[i].style.display="none";
		}
	}
}

// Tied nav to pageId by having the same name with nav prepended
function showNav(pageId) {
	navId = NAV + pageId;
	var navs = document.getElementsByClassName(NAV);
	for (var i = 0; i < navs.length; i++) {
		if (navs[i].id === (NAV + pageId)) {
			navs[i].className = NAV + " " + ACTIVE;
		} else {
			navs[i].className = NAV;
		}
	}
}

// Calls the proper update function given pageId
function update(pageId) {
	var func = pageId + "Update";
	window[func]();
}

function messagesUpdate() {
	check_hda_messages();
}

function chatUpdate() {
	get_message();
}

function homepageUpdate() {
	document.getElementById("queueErrorMessage").innerHTML = "";
}

function optionsUpdate() {
	// No updates needed for now
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

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getAnon"}
function get_anon() {
	var obj = JSON.stringify( {"mode":"getAnon"} );
	sendAjaxRequest(obj, get_anon_handler);
}

// Output: {"status":"OK","message":{"hdUser":"HDUser1","ticket_id":"24","anon_name":"Paul"}}
function get_anon_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			strMessage = strResponse.message;
			document.getElementById("chatroom_ticket_id").innerHTML =  "Name: " + strMessage["anon_name"] + 
				"Ticket Number: " + strMessage['ticket_id'] + ".";
			document.getElementById("queueErrorMessage").innerHTML = "";
			showPage("chat");
			get_message();
		} else {
			document.getElementById("queueErrorMessage").innerHTML = strResponse.message;
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getMessageForHDUser"}
function get_message() {
	var obj = JSON.stringify( {"mode":"getMessageForHDUser"} );
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
			document.getElementById("chatroom_ticket_id").innerHTML = "";
			document.getElementById("chatroom").innerHTML = strResponse.message;
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

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setMessageHDUser","strMessage":"a message"}
function set_message() {
	var message = document.getElementById("strMessage").value;
	var obj = JSON.stringify( {"mode":"setMessageHDUser","strMessage": message} );
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
		}
	}
}

function get_registered_users(){
	var obj = JSON.stringify( {"mode":"getRegistered"} );
	sendAjaxRequest(obj, get_registered_users_handler);
}

function get_registered_users_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var registered = strResponse.message;
			var options = "";
			for (var i = 0; i < registered.length; i++) {
				options += '<option value="' + registered[i] + '" />';
			}
			document.getElementById("registeredUsersChat").innerHTML = options;
			document.getElementById("registeredUsersMessages").innerHTML = options;
		} else {
			// Error
		}
	}
}


// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getPsid", "hdUser":"HDUser4"}
function get_private_session() {
	otherHDUser = document.getElementById("otherHDUser").value;
	document.getElementById("otherHDUser").value = "";
	var obj = JSON.stringify( {"mode":"getPsid", "hdUser": otherHDUser} );
	sendAjaxRequest(obj, get_private_session_handler);
}

function get_private_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			if (strResponse.message === "NO") {
				insert_new_private_session();
			} else {
				document.getElementById("message_chat").style.display = "inline";
				document.getElementById("message_request").style.display = "none";
				get_hda_message();
			}
		} else {
			// Error
			document.getElementById("messagesErrorMessage").innerHTML = strResponse.message;
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"insertNewPsid","hdUser1":"HDUser4", "hdUser2":"HDUser5"}
function insert_new_private_session() {
	var obj = JSON.stringify( {"mode":"insertNewPsid", "hdUser": otherHDUser} );
	sendAjaxRequest(obj, insert_new_private_session_handler);
}

function insert_new_private_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var obj = JSON.stringify( {"mode":"getPsid", "hdUser": otherHDUser} );
			sendAjaxRequest(obj, get_private_session_handler);
		} else {
			// Error: didn't do operation, already inserted?
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getHdaMessagesForHdaUser","hdUser":"HDUser1", "ps_id":"2"}
function get_hda_message() {
	var obj = JSON.stringify( {"mode":"getHdaMessagesForHdaUser","hdUser": userId} );
	sendAjaxRequest(obj, get_hda_message_handler);
}

function get_hda_message_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var txt = "";
			for (var i = 0; i < strResponse.message.length; i++) {
				txt += "<p>" + strResponse.message[i] + "</p>";
			}
			document.getElementById("message_chatroom").innerHTML = txt;
			
			setTimeout("get_hda_message()", CHECK_MESSAGE_TIMER);
		} else {
			// error handling
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"insertMessageForHda","hdUser":"HDUser1", "ps_id":"2", "strMessage":"hello"}
function set_hda_message() {
	var message = document.getElementById("strHduMessage").value;
	document.getElementById("strHduMessage").value = "";
	var obj = JSON.stringify( {"mode":"insertMessageForHda","hdUser": userId, "strMessage":message} );
	sendAjaxRequest(obj, set_hda_message_handler);
}

function set_hda_message_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			get_hda_message();
		} else {
			// error
		}
	}
}

function change_hduser_chat() {
	var obj = JSON.stringify( {"mode":"changePrivateSession"} );
	sendAjaxRequest(obj, change_hduser_chat_handler);
}

function change_hduser_chat_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			document.getElementById("message_chat").style.display = "none";
			document.getElementById("message_request").style.display = "inline";
			document.getElementById("message_chatroom").innerHTML = "";
			document.getElementById("messagesErrorMessage").innerHTML = "";
		} else {
			// error
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"checkHdaSession"}
function check_hda_messages() {
	var obj = JSON.stringify( {"mode":"checkHdaSession"} );
	sendAjaxRequest(obj, check_hda_messages_handler);
}

function check_hda_messages_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			var txt = "";
			for (var i = 0; i < strResponse.message.length; i++) {
				txt += "<p>" + strResponse.message[i] + "</p>";
			}
			document.getElementById("incoming_messages").innerHTML = txt;
			
			setTimeout("check_hda_messages()", CHECK_SESSION_TIMER);
		} else {
			// take out incoming messages response
			document.getElementById("incoming_messages").innerHTML = "";
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"transferSession", "hdUser":"HDUser2"}
function transfer_session() {
	var transferHduser = document.getElementById("transferHduser").value;
	document.getElementById("transferHduser").value = "";
	var obj = JSON.stringify( {"mode":"transferSession", "hdUser": transferHduser} );
	sendAjaxRequest(obj, transfer_session_handler);
}

function transfer_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			document.getElementById("transferErrorMessage").innerHTML = "";
			document.getElementById("chatroom_ticket_id").innerHTML = "";
			get_message();
		} else {
			// take out incoming messages response
			document.getElementById("transferErrorMessage").innerHTML = strResponse.message;
		}
	}
}

// localhost/HelpDesk/HDcontroller.php?obj={"mode":"endSessionForHDUser"}
function end_session() {
	var obj = JSON.stringify( {"mode":"endSessionForUser"} );
	sendAjaxRequest(obj, end_session_handler);
}

function end_session_handler() {
	if (xhrequest.readyState == 4 && xhrequest.status == 200) {
		strResponse = JSON.parse(xhrequest.responseText);
		if (strResponse.status == "OK") {
			document.getElementById("chatroom_ticket_id").innerHTML = "";
			showPage("homepage");
		} else {
			// anonymous user already ended the session
			document.getElementById("chatroom_ticket_id").innerHTML = "";
			showPage("homepage");
		}
	}
}