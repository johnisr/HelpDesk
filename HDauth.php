<?php
	require_once("HDmodel.php");
	require_once("HDcsrfg.php");
	require_once("HDconstants.php");
	session_start();
	
	// Already Logged in
	if (isset($_SESSION['userid'])) {
		echo "Already Logged in, " . $_SESSION["userid"];
		$url = $HELPDESK_URL;
		header( "Location: " . $url);
		exit();
	}
	
	$timedout = isIPTimedOUT($REMOTE_IP, $TIMEOUT_DURATION, $MAX_FAILED_ATTEMPTS);
	if ($timedout[1] === "YES") {
		$_SESSION["errorMessage"] = "Error: Too many Failed Login Attempts";
		echo "Error: Too many Failed Login Attempts";
		$url = $LOGIN_URL;
		header( "Location: " . $url);
		exit();
	}
	
	// First Time visiting the site
	if (!isset($_POST["username"]) || !isset($_POST["password"])) {
		$_SESSION["errorMessage"] = "Error: Help Desk Users Need to Log in";
		echo "Error: Help Desk Users Need to Log in";
		$url = $LOGIN_URL;
		header( "Location: " . $url);
		exit();
	}
	
	// A CSRFGuard does not exist, is not sent, or is not of the proper value
	if(!isset($_SESSION["CSRFGuard"]) || !isset($_POST["csrfg"]) || !validateCSRFGuard($_POST["csrfg"])) {
		$_SESSION["errorMessage"] = "Error: Invalid Form";
			echo "Error: Invalid Form";
			$url = $LOGIN_URL;
			header( "Location: " . $url);
			exit();
	}
	
	// Trying to log in
	$username = $_POST["username"];
	$password = $_POST["password"];
	$result = authenticate($username, $password);
	
	// Successful Login
	if ($result[1] == "OK") {
		session_regenerate_id(); // Give user a new session id (no destroying current)
		$_SESSION["userid"] = $username;
		echo "Valid Authentication";
		$url = $HELPDESK_URL;
		header( "Location: " . $url);
		exit();
	// Unsuccessful Login
	} else {
		$_SESSION["errorMessage"] = "Error: Invalid Authentication";
		updateIPFailedLogin($_SERVER["REMOTE_ADDR"]);
		echo "Error: Invalid Authentication";
		$url = $LOGIN_URL;
		header( "Location: " . $url);
		exit();
	}
?>