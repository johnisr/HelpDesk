<?php
	session_start();
	require_once("HDcsrfg.php");
	require_once("HDconstants.php");

	if (!isset($_SESSION["CSRFGuard"])) { returnError(); return; }
	if (!isset($_POST["csrfg"])) { returnError(); return; }
	if (!validateCSRFGuard($_POST["csrfg"])) { returnError(); return; }
	
	$uploadfile = $UPLOAD_DIR . basename($_FILES['userfile']['name']);

	//echo '<pre>';
	if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		$_SESSION["uploadMessage"] = "File is valid, and was successfully uploaded.";
	} else {
		$_SESSION["uploadMessage"] = "Possible file upload attack!";
	}
	$url = "anon.php";
	header( "Location: " . $url);
	

	//echo 'Here is some more debugging info:';
	//print_r($_FILES);

	//print "</pre>";
	
	// Generic error message for all errors
	function returnError() {
		$_SESSION["uploadMessage"] = "Improper Request Format";
		$url = "anon.php";
		header( "Location: " . $url);
	}

?>
