<?php
	session_start();
	require_once("HDmodel.php");
	require_once("HDcsrfg.php");
	require_once("HDconstants.php");
	
	// Requests should have obj JSON object
	if (!isset($_GET["obj"])) { returnError(); return; }
	$obj = json_decode($_GET["obj"], true);
	
	// Requests should have CSRFGuard value in obj
	if (!isset($obj["CSRFGuard"])) { returnError(); return; }
	if (!validateCSRFGuard($obj["CSRFGuard"])) { returnError(); return; }
	
	// Request should have mode value in obj
	if (!isset($obj["mode"])) { returnError(); return; }
	$mode = $obj["mode"];
	
	// Request mode value should be in array of supported functions
	if (!array_key_exists($mode, $function)) { returnError(); return; }
	
	// Given mode, call appropriate model function
	$result = call_user_func($function[$mode], $obj);
		
	// Given model result, create object to send to view
	$myObj = new stdClass;
	$myObj->status = $result[0];
	if (count($result) > 1) {
		// If there is a message to return, htmlescape it (string, array, assoc array)
		if (is_array($result[1])) {
			$myObj->message = array();
			if (isAssociativeArray($result[1])) {
				foreach($result[1] as $key=>$value) {
					$myObj->message[$key] = htmlspecialchars($value);
				}
			} else {
				foreach($result[1] as $row) {
					array_push($myObj->message, htmlspecialchars($row));
				}
			}
		} else {
			$myObj->message = htmlspecialchars($result[1]);
		}
	}
	
	// Put result object in JSON Format and send
	$myJSON = json_encode($myObj);
	echo $myJSON;
	return;
	
	// --------------------- Helper Functions --------------------------- //
	
	// Generic error message for all errors
	function returnError() {
		$myObj = new stdClass;
		$myObj->status = "ERROR";
		$myObj->message = "Improper request format";
		echo json_encode($myObj);
	}
	
	function isAssociativeArray(array $array) {
		return count(array_filter(array_keys($array), 'is_string')) > 0;
	}
?>