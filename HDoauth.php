<?php
	require_once("HDmodel.php");
	require_once("HDconstants.php");
	session_start();
	
	// Already Logged in
	if (isset($_SESSION['userid'])) {
		echo "Already Logged in, " . $_SESSION["userid"];
		$url = $HELPDESK_URL;
		header( "Location: " . $url);
		exit();
	}
	
	// First Time visiting the site	
	if (!isset($_GET["hash"])) {
		$nonce = createNonce($NUM_NONCE_BYTES);
		$_SESSION["nonce"] = $nonce;
		
		$back = $BACK_URL;
		$OAUTHLink = $OAUTH_URL;
		header( "Location: " . $OAUTHLink . "?nonce=" . $nonce . "&back=" . $back);
		exit();
	}
	
	// Trying to log in
	$nonce = $_SESSION["nonce"];
	unset($_SESSION["nonce"]);
		
	// echo "nonce: " . $nonce . "</p>";
	// echo "crypt " . crypt($SHARED_SECRET, $nonce). "</p>";
	// echo "hash from other " . $_GET["hash"]. "</p>";

	// Successful Login
	if (hash_equals(crypt($SHARED_SECRET, $nonce),  $_GET["hash"])) {
		session_regenerate_id(); // Give user a new session id (no destroying current)
		$_SESSION['userid'] = $_GET["user"];
		$registered = hasRegistered($_SESSION['userid']);
		if ($registered[1] == "NO") {
			insertHDUser($_SESSION['userid']);
		}
		echo "Valid Authentication";
		$url = $HELPDESK_URL;
		header( "Location: " . $url);
		exit();
	// Unsuccessful Login
	} else {
		echo "Error: Invalid Authentication";
		$url = $LOGIN_URL;
		header( "Location: " . $url);
		exit();
	}
	
	
	function createNonce($numBytes) {
		$nonce = openssl_random_pseudo_bytes($numBytes);
		return bin2hex($nonce);
	}
	
?>