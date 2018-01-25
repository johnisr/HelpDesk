<?php

	//HDcontroller constants, array that maps mode (a view request) to the proper model function
	$function = array (
		'setAnon'					=> 'setAnon',
		'num_queue'					=> 'numberInQueue',
		'exitQueue'					=> 'exitQueue',
		'getAnon'					=> 'getAnonymousFromQueue',
		'checkSessionAnon'			=> 'checkSessionForAnonymous',
		'endSessionForUser'			=> "endSessionForUser",
		'getSessionAnon'			=> 'getSessionForAnonymous',
		'setMessageAnon'			=> 'setMessageForAnonymous',
		'hasSessionEnded'			=> 'hasSessionEnded',
		'newAnonSession'			=> 'newAnonSession',
		'setMessageHDUser'			=> 'setMessageForHelpDeskUser',
		'getMessageForHDUser'		=> 'getMessageForHelpDeskUser',
		'transferSession'			=> 'transferSession',
		'getMessageForAnon'			=> 'getMessageForAnonymous',
		'numOnline'					=> 'getNumHelpDeskUserOnline',
		'listOnline'				=> 'listOnlineHelpDeskUsers',
		'listOffline'				=> 'listOfflineHelpDeskUsers',
		'getRegistered'				=> 'getRegisteredUsers',
		'getPsid'					=> 'getPrivateSessionIdHda',
		'insertNewPsid'				=> 'insertNewPrivateSessionId',
		'checkHdaSession'			=> 'checkForUpdatedHdaSession',
		'getHdaMessagesForHdaUser'	=> 'getHdaMessages',
		'insertMessageForHda'		=> 'setHdaMessage',
		'changePrivateSession'		=> 'changePrivateSession',
		'logoutHDUser'				=> 'logoutHDUser'
	);
	
	//HDauth Constants
	$HELPDESK_URL = "main.php";
	$LOGIN_URL = "login.php";
	$MAX_FAILED_ATTEMPTS = 5;
	$TIMEOUT_DURATION = 5;
	$REMOTE_IP = $_SERVER["REMOTE_ADDR"];

	// HDouath Constants
	$SHARED_SECRET = "0e3a880f1c2e81e89a05327d1ac9078f5db7b1beb3d90e6f265c627efdc6a4e5";
	$NUM_NONCE_BYTES = 32;
	// $HELPDESK_URL = "main.php";
	// $LOGIN_URL = "login.php";
	$OAUTH_URL = "https://www.eecs.yorku.ca/~johnisr/HelpDesk/HelpDeskAuth.cgi";
	$BACK_URL = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	
	// HDupload Constants
	$UPLOAD_DIR = 'upload/files/';
	
	// HDdao Constants, Initialization of database connection
	function initializeDBi() {
		$DB_HOST = "localhost";
		$DB_USER = "root";
		$DB_PASSWORD = "";
		$DB_NAME = "help desk";
		$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);

		return $mysqli;
	}

?>