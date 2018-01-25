<?php
	require_once("helpdeskDAO.php");

	$mode = $_GET["mode"];
	
	// Use Case: 	HDUser wants to log in
	// DAO method:	getHDPassword
	// Implement:	client sends user/pass -> HDChatter2 receives/validates input then sends 
	//				DAO method request, compares, then allows login
	if ($mode === "login") {
		// localhost/HelpDesk/testDAO.php?mode=login&hdUsername=HDUser1&hdPassword=HDUser1Pass
		$username = $_GET["hdUsername"];
		$password = $_GET["hdPassword"];
		
		$storedPass = mysql_fetch_array(getHDPassword($username));
		if (!$storedPass) {
			echo "<p>'$username' is an invalid username!</p>";
		} else if ($storedPass[0] != $password) {
			echo "<p>'$password' is an invalid password!</p>";
		} else {
			echo "<p>'$username' with '$password' is valid!</p>";
		}
	}
	
	// Use Case: 	Anon wants to enter queue
	// DAO method:	setAnonToQueue
	// Implement:	client sends sessionID/name -> HDChatter2 receives/validates input ->  
	//				puts him in Queue
	if ($mode === "setAnon") {
		// localhost/HelpDesk/testDAO.php?mode=setAnon&sessionId=11&strUserId=someName
		$sessionId = $_GET["sessionId"];
		$strUserId = $_GET["strUserId"];
		setAnonToQueue($sessionId, $strUserId);
		
		$result = mysql_fetch_array(getTicketId($sessionId));
		$ticketNumber = $result["ticket_id"];
		echo "<p> You have ticket number: " . $ticketNumber;
	}
	
	// Use Case: 	Anon User Waiting in Queue; Wants to know place
	// DAO method:	getTicketId, getQueueNumber
	// Implement:	client sends sessionID -> HDChatter2 receives/validates input -> uses getTicketId 
	//				and getQueueNumber to get place -> return result
	if ($mode === "num_queue") {
		// localhost/HelpDesk/testDAO.php?mode=num_queue&session_id=20
		$session_id = $_GET["session_id"];
		
		$result = mysql_fetch_array(getTicketId($session_id));
		$ticket_id = $result["ticket_id"];
		
		if (!$ticket_id) {
			echo "<p>You are not in queue!</p>";
		} else {
			$result = getQueueNumber($ticket_id);
			echo "<p> You are in position: '$result'";
		}	
	}
	
	// Use Case: 	HDUser wants to take anon from queue
	// DAO method:	getAnonFromQueue, setSession
	// Implement:	HDUser clicks button to take -> Server runs getAnonFromQueue to take from queue
	//				Then sets a new session with anon taken
	// Notes:		Make sure HDChatter 2 checks if queue empty
	if ($mode === "getAnon") {
		// localhost/HelpDesk/testDAO.php?mode=getAnon&hdUser=HDUser1
		$hdUser = $_GET["hdUser"];
		
		$arrRecords = mysql_fetch_array(getAnonFromQueue());
		$anonName = $arrRecords["anon_name"];
		$ticket_id = $arrRecords["ticket_id"];
		$session_id = $arrRecords["session_id"];
		
		setSession($ticket_id, $hdUser, $session_id, $anonName);
		
		echo "<p>" . $hdUser . ", you are now serving " . $anonName;
		echo " who has ticket number " . $ticket_id . "</p>";
	}
	
	// Use Case: 	HDUser wants to end a session
	// DAO method:	endSession
	// Implement:	HDUser clicks button to end -> Server runs endSession
	// Notes : 		May need to add endSessionForAnon (where javascript event of him browsing away)
	if ($mode == "endSession") {
		// localhost/HelpDesk/testDAO.php?mode=endSession&ticket_id=15
		$ticket_id = $_GET['ticket_id'];
		
		endSession($ticket_id);
	}
	
	// Use Case: 	Anonymous User asks repeatedly if he's in a session
	// DAO method:	getSessionForAnon
	// Implement:	Anon sends sessionId -> Server checks if sessionId in Session, if found
	//				returns OK (have handler move to proper page), else do nothing
	// Notes:		Just a random arbitrary session_id, the real id will the php_session id
	if ($mode == "getSessionAnon") {
		// localhost/HelpDesk/testDAO.php?mode=getSessionAnon&session_id=11
		$session_id = $_GET['session_id'];

		$arrRecords = mysql_fetch_array(getSessionForAnon($session_id));
		$anonName = $arrRecords["anon_name"];
		$hdUser = $arrRecords["HDuser"];
		$ticket_id = $arrRecords["ticket_id"];
		
		echo "<p>" . $anonName . ", you are being served by " .  $hdUser . 
			" and has ticket_id " . $ticket_id . "</p>";
	}
	
	// Use Case: 	Anon User wants to send a message
	// DAO method:	getSessionForAnon, setMessageForAnon
	// Implement:	Anon sends sessionId/message -> Server finds ticket_id associated with session_id,
	//				inserts the message into messages with the proper ticket_id, have chat update after
	if ($mode == "setMessageAnon") {
		// localhost/HelpDesk/testDAO.php?mode=setMessageAnon&session_id=11&strMessage=hello
		$session_id = $_GET["session_id"];
		$arrRecords = mysql_fetch_array(getSessionForAnon($session_id));
		$ticket_id = $arrRecords["ticket_id"];
		$strUserId = $arrRecords["anon_name"];
		
		$strMessage = $_GET["strMessage"];
		
		setMessage($ticket_id, $strUserId, $strMessage);
		
	}

	// Use Case: 	HDUser wants to know which sessions he belongs to
	// DAO method:	getSessionForHdUser
	// Implement:	HDUser sends username -> Server checks for all sessions that have not ended,
	//				gets ticket_id of the first session HDUser belongs to
	// Notes:		The getSessionForHdUser function returns all unclosed tickets. the below implementation
	// 				only returns the first one, though can be extended for all tickets he has taken on.
	// 				Should ticket_ids be stored client side after this or client keeps asking
	if ($mode == "getSessionHDUser") {
		// localhost/HelpDesk/testDAO.php?mode=getSessionHDUser&hdUser=HDUser1
		$hdUser = $_GET["hdUser"];
		
		$arrRecords = mysql_fetch_array(getSessionForHdUser($hdUser));
		$anonName = $arrRecords["anon_name"];
		$hdUser = $arrRecords["HDuser"];
		$ticket_id = $arrRecords["ticket_id"];
		
		echo "<p>" . $hdUser . ", you are serving " .  $anonName . 
			" and has ticket_id " . $ticket_id . "</p>";
		
	}
	
	// Use Case: 	HDUser wants to send a message to a specific ticketId
	// DAO method:	getSessionForHdUser, setMessage
	// Implement:	HDUser sends name/message -> Server checks for all sessions associated with him -> takes the first one
	//				and enters in the message
	// Notes:		As it also uses getSessionForHdUser, need to know if ticket_id is safe to store client side
	// 				If yes, process can be simplefied by using setMEssage($HDuser, $ticket_id, $content)
	//				If not, may lose functionality of HDUser serving multiple people (requires a lot of workaround)
	if ($mode == "setMessageHDUser") {
		// localhost/HelpDesk/testDAO.php?mode=setMessageHDUser&hdUser=HDUser1&strMessage=hello
		$hdUser = $_GET["hdUser"];
		$arrRecords = mysql_fetch_array(getSessionForHdUser($hdUser));
		$ticket_id = $arrRecords["ticket_id"];

		$strMessage = $_GET["strMessage"];
		
		setMessage($ticket_id, $hdUser, $strMessage);
		
	}
	
	// Use Case: 	a user wants to retrieve message
	// DAO method:	getMessagesFromSession
	// Implement:	1. HDUser sends username or ticket -> Server uses ticket to get all message
	//				(or username -> ticket first via getSessionForHdUser)
	//				2. anon sends sessionId -> Server uses getSessionAnon to get ticketId, uses ticket to
	//				get all messages
	if ($mode == "getMessage") {
		// localhost/HelpDesk/testDAO.php?mode=getMessage&ticket_id=1 [or 11 for above]
		$ticket_id = $_GET['ticket_id'];
		$dbRecords = getMessagesFromSession($ticket_id);
		
		while ($arrRecords = mysql_fetch_array($dbRecords)) {
			$time = date("H:i:s", strtotime($arrRecords["Timestamp"]));
			echo "<p>[" . $time . "][" . $arrRecords["Sender"] . "]: ";
			echo $arrRecords["Content"] . "</p>";
		}
	}
	
	// Use Case: 	HDUser wants to transfer session to another HDUser
	// DAO method:	getSessionForHdUser, transferSessionTo
	// Implement:	HDUser sends name/HDUser -> Server uses getSession to find ticket_id, uses transferSession
	//				to transfer to another HDUser
	// Notes:		Can use listOnline to figure out who's online for HDUsers (as such they'll have access to HDUser)
	// 				Again, more work going from HDUser -> ticket_id, really needed?
	if ($mode =="transferSession") {
		// localhost/HelpDesk/testDAO.php?mode=transferSession&ticket_id=22&hdUser=HDUser2
		$ticket_id = $_GET['ticket_id'];
		$hdUser = $_GET['hdUser'];
		
		transferSessionTo($ticket_id, $hdUser);
	}
	
	// Use Case: 	Anon waiting in queue wants to know how many help desk users online
	// DAO method:	getNumHduOnline
	// Implement:	HDUser sends request -> Server outputs info
	if ($mode == "numOnline") {
		// localhost/HelpDesk/testDAO.php?mode=numOnline
		echo mysql_num_rows(getNumHduOnline());
	}
	
	// Use Case: 	HDUser wants a list of online users (to click and send messsages to)
	// DAO method:	getOnlineList
	// Implement:	HDUser sends request -> Server sends list back -> js populates "online list" with results
	if ($mode == "listOnline") {
		// localhost/HelpDesk/testDAO.php?mode=listOnline
		$dbRecords = getOnlineList();
		while ($arrRecords = mysql_fetch_array($dbRecords)) {
			echo "<p>". $arrRecords["username"] . "</p>";
		}
	}
	
	// Use Case: 	HDUser wants a list of offline users (just to have a dropdown list of offline users)
	// DAO method:	getOfflineList
	// Implement:	HDUser sends request -> Server sends list back -> js populates "offline list" with results
	if ($mode == "listOffline") {
		// localhost/HelpDesk/testDAO.php?mode=listOffline
		$dbRecords = getOfflineList();
		while ($arrRecords = mysql_fetch_array($dbRecords)) {
			echo "<p>". $arrRecords["username"] . "</p>";
		}
	}
	
	// Use Case: 	HDUser needs to know ps_id of session where HDUser last talked to another HDUser
	// DAO method:	getPSidHdaMessage
	// Implement:	HDUser sends HDUser1/HDUser2 -> Server uses method to get ps_id -> send ps_id
	if ($mode == "getPsid") {
		// localhost/HelpDesk/testDAO.php?mode=getPsid&hdUser1=HDUser2&hdUser2=HDUser4
		$hdUser1 = $_GET["hdUser1"];
		$hdUser2 = $_GET["hdUser2"];
		
		$result = getPSidHdaMessage($hdUser1, $hdUser2);
		
		if (mysql_num_rows($result) == 0) {
			echo "<p>looks like you haven't started a pm with " . $hdUser2 . " yet.</p>";
		} else {
			$arrRecords = mysql_fetch_array($result);
			echo $arrRecords["ps_id"];
		}
	}
	
	// Use Case: 	HDUser1 has never talked to HDUser2 and wants to start
	// DAO method:	getPSidHdaMessage, insertNewPsid, setMessageForHda, setHdaSessionUpdatedForOther
	// Implement:	HDUser sends HDUser1/HDUser2 -> Server uses method to get ps_id and finds none ->
	//				Returns no result, display empty chat "No messages", give prompt to enter message ->
	//				If message entered do (insertNewPsid, setMessageForHda, setHdaSessionUpdatedForOther)
	if($mode == "insertNewPsid") {
		// localhost/HelpDesk/testDAO.php?mode=insertNewPsid&hdUser1=HDUser1&hdUser2=HDUser3
		$hdUser1 = $_GET["hdUser1"];
		$hdUser2 = $_GET["hdUser2"];
		
		$result = getPSidHdaMessage($hdUser1, $hdUser2);
		// Should be true as testing inserting of psid that does not exist
		if (mysql_num_rows($result) == 0) {
			insertNewPsid($hdUser1, $hdUser2);
			echo "<p>Inserted</p>";
		} else {
			echo "<p>Test Failed</p>";
		}
	}
	
	// Use Case: 	HDUser1 wants to check if any message has been sent by another HDuser
	// DAO method:	checkHdaSession
	// Implement:	HDUser sends username -> Server uses username to find any session on that has boolean
	//				that needs HDUser to update -> gets ps_id and name of other hduser -> can use that to
	//				either show a change in online/offline list
	if ($mode == "checkHdaSession") {
		// localhost/HelpDesk/testDAO.php?mode=checkHdaSession&hdUser=HDUser1
		$hdUser = $_GET["hdUser"];
		echo "<p>Checking hda_session for new messages for " . $hdUser . ".</p>";
		$result = checkHdaSession($hdUser);
		if (mysql_num_rows($result) == 0) {
			echo "<p>No messages available.</p>";
		}
		while ($arrRecords = mysql_fetch_array($result)) {
			echo "<p>Need to check ps_id ". $arrRecords["ps_id"] . ". ";
			if ($arrRecords["hdAccount1"] == $hdUser) {
				echo $arrRecords["hdAccount2"] . " wants to talk to you.</p>";
			} else {
				echo $arrRecords["hdAccount1"] . " wants to talk to you.</p>";
			}
		}
	}
	
	// Use Case: 	HDUser1 wants to get messages of a ps_id
	// DAO method:	getMessagesFromHda, setHdaSessionReceived
	// Implement:	HDUser sends username and ps_id (e.g. from checkHdaSession) -> Server uses getMessagesFromHda
	// 				To retrieve all messages from that ps_id -> change updated status to 0 (since already updated)
	//				and to stop showing change in online/offline list
	if ($mode == "getHdaMessagesForHdaUser") {
		// localhost/HelpDesk/testDAO.php?mode=getHdaMessagesForHdaUser&hdUser=HDUser1&ps_id=2
		$hdUser = $_GET["hdUser"];
		$ps_id = $_GET["ps_id"];
		
		$dbRecords = getMessagesFromHda($ps_id);
		while ($arrRecords = mysql_fetch_array($dbRecords)) {
			$time = date("H:i:s", strtotime($arrRecords["Timestamp"]));
			echo "<p>[" . $time . "][" . $arrRecords["Sender"] . "]: ";
			echo $arrRecords["Content"] . "</p>";
		}
		
		setHdaSessionReceived($ps_id, $hdUser);
		
	}
	
	// Use Case: 	HDUser1 wants to send messages of a ps_id
	// DAO method:	setMessageForHda, setHdaSessionUpdatedForOther
	// Implement:	HDUser sends username, message,  and ps_id (e.g. from checkHdaSession) -> Server uses 
	// 				setMessageForHda to insert a message with proper ps_id -> use setHdaSessionUpdatedForOther
	//				to make sure the other use will get an update
	if ($mode == "insertMessageForHda") {
		// localhost/HelpDesk/testDAO.php?mode=insertMessageForHda&hdUser=HDUser1&ps_id=2&strMessage=hello
		$ps_id = $_GET["ps_id"];
		$hdUser = $_GET["hdUser"];
		$strMessage = $_GET["strMessage"];
		
		setMessageForHda($ps_id, $hdUser, $strMessage);
		
		setHdaSessionUpdatedForOther($ps_id, $hdUser);
	}
	
?>