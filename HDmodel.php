<?php
	require_once("HDdao.php");

	// -----------------------------Server to Server methods -------------------------------- //
	
	function authenticate($username, $password) {
		//Setup Result Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		
		// Use appropriate DAO function
		try {
			$res = getHDPasswordi($username); 
			$storedPass = $res->fetch_assoc();
			$output[0] = "OK";
			if ( password_verify($password, $storedPass["password"])) {
				$output[1] = "OK";
			} else {
				$output[1] = "NO";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function logError($errorMessage) {
		$output = array("ERROR", "An Error Has Occured");
		error_log($errorMessage);
		return $output;
	}
	
	function hasRegistered($username) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		
		
		// Use appropriate DAO function
		try {
			$res = hdUserExisti($username);
			$output[0] = "OK";
			if ($res->num_rows == 0) {
				$output[1] = "NO";
			} else {
				$output[1] = "YES";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function insertHDUser($username) {
		//Setup Result Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		
		
		// Use appropriate DAO function
		try {
			setHDUseri($username);
			$output[0] = "OK";
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function isIPTimedOut($ip, $minutes, $attempts) {
		try {
			$res = resetAttemptsIfPastTimeOut($ip, $minutes);
			if ($res->affected_rows > 0) {
				$output[0] = "OK";
				$output[1] = "NO";
			} else {
				$res = hasExceededMaxAttempts($ip, $attempts);
				$output[0] = "OK";
				if ($res->num_rows > 0) {
					$output[1] = "YES";
				} else {
					$output[1] = "NO";
				}
			}
			return $output;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
	}
	
	function updateIPFailedLogin($ip) {
		try {
			$res = inFailedLogin($ip);
			if ($res->num_rows == 0) {
				$res = insertFailedLogin($ip);
			} else {
				$res = updateFailedLogin($ip);
			}
			$output [0] = "OK";
			return $output;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
	}
	
	// ---------------------------CLient to Client /HDcontroller methods -------------------------- //
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setAnon","strUserId":"Rachel"}
	// Output: {"status":"OK"}
	function setAnon($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		if (!isset($obj["strUserId"])) {
			$output[0] = "ERROR";
			$output[1] = "Enter a user name";
			return $output;
		}
		$strUserId = $obj["strUserId"];
		$sessionId = session_id();
		
		// Use appropriate DAO function
		try {
			$res = anonInQueue($sessionId);
			if ($res->num_rows === 0) {
				setAnonToQueuei($sessionId, $strUserId);
				$_SESSION["bookmark"] = "queue";
				$output[0] = "OK";
			} else {
				$output[0] = "ERROR";
				$output[1] = "Already in queue";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"num_queue"}
	// Output: {"status":"OK","message":"'4'"}
	function numberInQueue($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		$session_id = session_id();
		
		// Use appropriate DAO function
		try {
			$res = getTicketIdi($session_id);
			$row = $res->fetch_assoc();
			$ticket_id = $row["ticket_id"];
		
			if (!$ticket_id) {
				$output[0] = "ERROR";
				$output[1] = "You are not in queue!";
			} else {
				$res = getQueueNumberi($ticket_id)->num_rows;
				$output[0] = "OK";
				$output[1] = $res;
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function exitQueue($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		$session_id = session_id();
		
		// Use appropriate DAO function
		try {
			deleteAnonFromQueuei($session_id);
			$_SESSION["bookmark"] = "exit";
			$output[0]="OK";
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function newAnonSession($obj) {
		
		try {
			session_regenerate_id();
			$_SESSION["bookmark"] = "login";
			$output[0]="OK";
			return $output;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"checkSessionAnon"}
	function checkSessionForAnonymous($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		$session_id = session_id();
		
		// Use appropriate DAO function
		try {
			$res = checkSessionForAnoni($session_id);
			$output[0] = 'OK';
			if ($res->num_rows > 0) {
				$output[1] = "YES";
				$row = $res->fetch_assoc();
				$_SESSION["ticket_id"] = $row["ticket_id"];
				$_SESSION["anon_name"] = $row["anon_name"];
				$_SESSION["bookmark"] = "chat";
			} else {
				$output[1] = "NO";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getAnon"}
	// Output: {"status":"OK","message":{"hdUser":"HDUser1","ticket_id":"24","anon_name":"Paul"}}
	function getAnonymousFromQueue($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		$hdUser = $_SESSION["userid"];
		
		
		// Use appropriate DAO function
		try {
			if (isset($_SESSION["ticket_id"])) {
				$output[0] = "ERROR";
				$output[1] = "You already have an ongoing session";
			} else {
				$res = getSessionForHdUseri($hdUser);
			
				// Check if there are any existing sessions not ended (i.e. transfer)
				if ($res->num_rows != 0) {
					$row = $res->fetch_assoc();
					$anonName = $row["anon_name"];
					$ticket_id = $row["ticket_id"];
				} else {
					$res = getAnonFromQueuei();
					
					// Both session and queue are empty
					if ($res->num_rows == 0) {
						$output[0] = "ERROR";
						$output[1] = "No Users in queue";
						return $output;
					// Get first person from queue
					} else {
						$row = $res->fetch_assoc();
						$anonName = $row["anon_name"];
						$ticket_id = $row["ticket_id"];
						$session_id = $row["session_id"];
						
						setSessioni($ticket_id, $hdUser, $session_id, $anonName);		
					}
					
				}
				
				$_SESSION["ticket_id"] = $ticket_id;
				$output[0] = "OK";	
				
				$array = array();
				$array['hdUser'] = $hdUser;
				$array['ticket_id'] = $ticket_id;
				$array['anon_name'] = $anonName;
				$output[1] = $array;
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"endSessionForHDUser"}
	function endSessionForUser($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		
		// Use appropriate DAO function
		try {
			if (isset($_SESSION["ticket_id"])) {
				$ticket_id = $_SESSION["ticket_id"];
			
				endSessioni($ticket_id);
				$output[0] = "OK";
				
				unset($_SESSION["ticket_id"]);
			} else {
				$output[0] = "ERROR";
				$output[1] = "No Session to End.";
			}
			if (!isset($_SESSION["userid"])) {
				$_SESSION["bookmark"] = "exit";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	function hasSessionEnded($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		// validate passed variables
		
		
		// Use appropriate DAO function
		try {
			if (isset($_SESSION["ticket_id"])) {
				$ticket_id = $_SESSION["ticket_id"];
				$res = sessionEndedi($ticket_id);
				$output[0] = "OK";
				
				if ($res->num_rows != 0) {
					$output[1] = "Ended";
					unset($_SESSION["ticket_id"]);
				} else {
					$output[1] = "Not Ended.";
				}
			} else {
				$output[0] = "Error";
				$output[1] = "No Session to End.";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setMessageAnon", "strMessage":"new message"}
	// Output: {"status":"OK"}
	function setMessageForAnonymous($obj) {
		$session_id = session_id();
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($obj["strMessage"])) {
			$output[0] = "ERROR";
			$output[1] = "Enter a message";
			return $output;
		}
		if (!isset($_SESSION["ticket_id"]) || !isset($_SESSION["anon_name"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		
		// Use appropriate DAO function
		try {
			$ticket_id = $_SESSION["ticket_id"];
			$strUserId = $_SESSION["anon_name"];
			$strMessage = $obj["strMessage"];
			setMessagei($ticket_id, $strUserId, $strMessage);
			$output[0] = "OK";
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"setMessageHDUser","strMessage":"a message"}
	// Output: {"status":"OK"}
	function setMessageForHelpDeskUser($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"]) || !isset($_SESSION["ticket_id"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($obj["strMessage"])) {
			$output[0] = "ERROR";
			$output[1] = "Enter a message";
			return $output;
		}
		
		$hdUser = $_SESSION["userid"];
		
		// Use appropriate DAO function
		try {
			$ticket_id = $_SESSION["ticket_id"];

			$strMessage = $obj["strMessage"];
			
			setMessagei($ticket_id, $hdUser, $strMessage);
			$output[0] = "OK";
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
		
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getMessageForAnon"}
	// Output: {"status":"OK","message": [ "[00:00:00][HDUser1]: hello","[00:00:00][HDUser1]: hello", ... ]}
	function getMessageForAnonymous($obj) {
		$session_id = session_id();
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		$session_id = session_id();
		if (!isset($_SESSION["ticket_id"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		
		// Use appropriate DAO function
		try {
			$ticket_id = $_SESSION["ticket_id"];
			$res = getMessagesFromSessioni($ticket_id);
			$output[0] = "OK";
			
			$array = array();
			while ($row = $res->fetch_assoc()) {
				$time = date("H:i:s", strtotime($row["Timestamp"]));
				$sender = $row["Sender"];
				$content = $row["Content"];
				$message =  "[" . $time . "][" . $sender . "]: ". $content;
				array_push($array, $message);
			}
			$output[1] = $array;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getMessageForHDUser"}
	function getMessageForHelpDeskUser($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		
		$hdUser = $_SESSION["userid"];
		
		// Use appropriate DAO function
		try {
			if (isset($_SESSION["ticket_id"])) {
				$ticket_id = $_SESSION["ticket_id"];
				$res = getMessagesFromSessioni($ticket_id);
				$output[0] = "OK";
				
				$array = array();
				while ($row = $res->fetch_assoc()) {
					$time = date("H:i:s", strtotime($row["Timestamp"]));
					$sender = $row["Sender"];
					$content = $row["Content"];
					$message =  "[" . $time . "][" . $sender . "]: ". $content;
					array_push($array, $message);
				}
				$output[1] = $array;
			} else {
				$output[0] = "ERROR";
				$output[1] = "No Chat Session started.";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"transferSession", "hdUser":"HDUser2"}
	// Output: {"status":"OK"}
	function transferSession($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($obj['hdUser'])) {
			$output[0] = "ERROR";
			$output[1] = "No user specified";
			return $output;
		}
		
		$hdUser1 = $_SESSION["userid"];
		$hdUser2 = $obj['hdUser'];
		
		// Use appropriate DAO function
		try {
			$res = hdUserExisti($hdUser2);
			if ($res->num_rows == 0) {
				$output[0] = "ERROR";
				$output[1] = "Error: The user " . $hdUser2 . " does not exist.";
			} else {
				$ticket_id = $_SESSION['ticket_id'];
				transferSessionToi($ticket_id, $hdUser2);
				unset($_SESSION["ticket_id"]);
				$output[0] = "OK";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	function getRegisteredUsers($obj) {
		$output = array();
		
		try {
			$res = getRegisteredListi();
			$output[0] = "OK";
			$array = array();
			while ($row = $res->fetch_assoc()) {
				array_push($array, $row["username"]);
			}
			$output[1] = $array;
			return $output;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"numOnline"}
	// {"status":"OK","message":2}
	function getNumHelpDeskUserOnline($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// Use appropriate DAO function
		try {
			$res = getNumHduOnline();
			$output[0] = "OK";
			$output[1] = $res->num_rows;
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getPsid", "hdUser":"HDUser4"}
	// Output: {"status":"OK","message":"3"}
	function getPrivateSessionIdHda ($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($obj['hdUser'])) {
			$output[0] = "ERROR";
			$output[1] = "No user specified";
			return $output;
		}
		
		$hdUser1 = $_SESSION["userid"];
		$hdUser2 = $obj["hdUser"];
		
		// Use appropriate DAO function
		try {
			$res = hdUserExisti($hdUser2);
			if ($res->num_rows == 0) {
				$output[0] = "ERROR";
				$output[1] = "Error: The user " . $hdUser2 . " does not exist.";
			} else {
				$res = getPSidHdaMessagei($hdUser1, $hdUser2);
				$output[0] = "OK";
				
				if ($res->num_rows == 0) {
					$output[1] = "NO";
				} else {
					$row = $res->fetch_assoc();
					$_SESSION["ps_id"] = $row["ps_id"];
					$output[1] = $row["ps_id"];
				}
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"insertNewPsid", "hdUser":"HDUser5"}
	function insertNewPrivateSessionId($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($obj['hdUser'])) {
			$output[0] = "ERROR";
			$output[1] = "No user specified";
			return $output;
		}
		
		$hdUser1 = $_SESSION["userid"];
		$hdUser2 = $obj["hdUser"];
		
		// Use appropriate DAO function
		try {
			if ($hdUser1 == $hdUser2) {
				$output[0] = "ERROR";
				$output[1] = "Cannot start a session with yourself.";
			} else {
				$res = hdUserExisti($hdUser2);
				if ($res->num_rows == 0) {
					$output[0] = "ERROR";
					$output[1] = "The user " + $hdUser2 + " does not exist.";
				} else {
					$res = getPSidHdaMessagei($hdUser1, $hdUser2);
					$output[0] = "OK";
					$output[0] = "OK";
					if ($res->num_rows == 0) {
						insertNewPsidi($hdUser1, $hdUser2);
						$temp = getPSidHdaMessagei($hdUser1, $hdUser2);
						$row = $temp->fetch_assoc();
						$output[1] = $row["ps_id"];
					} else {
						$output[0] = "ERROR";
						$output[1] = "Test Failed";
					}
				}
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"checkHdaSession","hdUser":"HDUser1"}
	// Output: {"status":"OK","message":["HDUser3: ps_id 2","HDUser5: ps_id 8"]}
	function checkForUpdatedHdaSession($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		
		$hdUser = $_SESSION["userid"];
		
		// Use appropriate DAO function
		try {
			$res = checkHdaSessioni($hdUser);
			$output[0] = "OK";
			
			if ($res->num_rows == 0) {
				$array = array();
				array_push($array, "No new messages available.");
				$output[1] = $array;
			} else {
				$array = array();
				while ($row = $res->fetch_assoc()) {
					if ($row["hdAccount1"] == $hdUser) {
						array_push($array, $row["hdAccount2"]. " chat has unread messages" );
					} else {
						array_push($array, $row["hdAccount1"]. " chat has unread messages" );
					}
				}
				$output[1] = $array;
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"getHdaMessagesForHdaUser","hdUser":"HDUser1", "ps_id":"2"}
	// Output: {"status":"OK","message":[ "[10:50:36][HDUser1]: hello","[10:51:10][HDUser1]: world, ... ]"
	function getHdaMessages($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		if (!isset($_SESSION["userid"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($_SESSION["ps_id"])) {
			$output[0] = "ERROR";
			$output[1] = "Enter a ps_id";
			return $output;
		}
		
		$hdUser = $_SESSION["userid"];
		$ps_id = $_SESSION["ps_id"];
		
		// Use appropriate DAO function
		try {
			$res = getMessagesFromHdai($ps_id);
			$output[0] = "OK";
			
			$array = array();
			while ($row = $res->fetch_assoc()) {
				$time = date("H:i:s", strtotime($row["Timestamp"]));
				$sender = $row["Sender"];
				$content = $row["Content"];
				$message =  "[" . $time . "][" . $sender . "]: ". $content;
				array_push($array, $message);
			}
			$output[1] = $array;
			
			setHdaSessionReceivedi($ps_id, $hdUser);
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		return $output;
	}
	
	// localhost/HelpDesk/HDcontroller.php?obj={"mode":"insertMessageForHda", "strMessage":"hello"}
	function setHdaMessage($obj) {
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		if (!isset($_SESSION["userid"]) || !isset($_SESSION["ps_id"])) {
			$output[0] = "ERROR";
			$output[1] = "Invalid operation";
			return $output;
		}
		if (!isset($obj["strMessage"])){
			$strMessage = $obj["strMessage"];
		}
		
		$ps_id = $_SESSION["ps_id"];
		$hdUser = $_SESSION["userid"];
		$strMessage = $obj["strMessage"];
		
		
		if (!isset($_SESSION["ps_id"])) {
			$output[0] = "ERROR";
			$output[1] = "Enter a ps_id";
			return $output;
		}
		
		
		// Use appropriate DAO function
		try {
			setMessageForHdai($ps_id, $hdUser, $strMessage);
			$output[0] = "OK";
			
			setHdaSessionUpdatedForOtheri($ps_id, $hdUser);
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		return $output;
	}
	
	function changePrivateSession($obj) {
		
		if (!isset($_SESSION["ps_id"])) {
			$output[0] = "ERROR";
			$output[1] = "No private session started";
			return $output;
		}
		unset($_SESSION["ps_id"]);
		
		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		
		
		// Use appropriate DAO function
		$output[0] = "OK";
		
		return $output;
	}
	
	function logoutHDUser($obj) {

		//Setup output Array (index 0 for OK or Error, index 1 for proper return or ErrMsg);
		$output = array();
		
		// validate passed variables
		try {
			if (isset($_SESSION["userid"])) {
				$output[0] = "OK";
				session_destroy();
				session_start();
			} else {
				$output[0] = "ERROR";
				$output[1] = "Not a Help Desk User";
			}
		} catch (Exception $e) {
			return logError($e->getMessage());
		}
		
		// Use appropriate DAO function
		
		
		return $output;
	}
?>