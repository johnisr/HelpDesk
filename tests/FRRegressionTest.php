<?php
	/* 
		Does a regression test, making sure all functional requirements are met.
		Conditions: 
			(1) messages, session, hda_messages, hda_session must be empty. SQL code provided below.
				DELETE FROM `messages` WHERE 1; DELETE FROM session where 1; DELETE FROM queue where 1;
				DELETE FROM hda_message where 1; DELETE FROM hda_session where 1;
			(2) The Help desk users and passwords must be in hdaccounts
			(3) Go into HDauth and comment out session_regenerate_id() when successful login
			(4) FOR SSL, make sure to add CURLOPT_SSL_VERIFYPEER to false and change the url to https
		
	*/
	
	// Variables Used, Check if name/pass in hdaccounts
	$anon_name = "Bob";
	$hdu1_name = "zxcv";
	$hdu2_name = "HDUser2";
	$hdu1_pass = "z";
	$hdu2_pass = "HDUser2Pass";

	// Create 3 sessions for the 1 anonymous, 2 hdusers
	session_start();
	
	$anon_sid = session_id();
	session_regenerate_id();
	$hdu1_sid = session_id();
	session_regenerate_id();
	$hdu2_sid = session_id();
	session_regenerate_id();
	
	// echo "anon_sid is: " . $anon_sid . "<p>";
	// echo "hdu1_sid is: " . $hdu1_sid . "<p>";
	// echo "hdu2_sid is: " . $hdu2_sid . "<p>";
	
	// ------------------------TESTS START---------------------------------
	
	// authentication test
	if (!hdu_login($hdu1_name."a", $hdu1_pass, "Error: Invalid Authentication")) { echo "FAILED INVALID NAME LOGIN ATTEMPT"; return;}
	if (!hdu_login($hdu1_name, $hdu1_pass."a", "Error: Invalid Authentication")) { echo "FAILED INVALID PASS LOGIN ATTEMPT"; return;}
	if (!hdu_login($hdu1_name."a", $hdu1_pass."a", "Error: Invalid Authentication")) { echo "FAILED INVALID NAME PASS LOGIN ATTEMPT"; return;}
	if (!hdu_login($hdu1_name, $hdu1_pass, "Valid Authentication")) { echo "FAILED VALID LOGIN HDU1 ATTEMPT"; return; }
	if (!hdu_login($hdu2_name, $hdu2_pass, "Valid Authentication")) { echo "FAILED VALID LOGIN HDU2 ATTEMPT"; return; }
	
	// anon entering queue and hdu1 accepting
	if (!anon_enter_queue("OK")) { echo "ANON FAILED TO ENTER QUEUE"; return; }
	if (!anon_check_queue_num("OK", 1)) { echo "ANON FAILED TO GET QUEUE NUMBER"; return; }
	if (!anon_check_session("NO")) { echo "ANON FAILED TO CHECK SESSION"; return; }
	if (!hdu_pop_queue($hdu1_name, "OK")) { echo "HDU1 FAILED TO GET FROM QUEUE"; return; }
	if (!anon_check_queue_num("ERROR", "You are not in queue!")) { echo "ANON FAILED TO SEE ANON LEFT QUEUE"; return; }
	if (!anon_check_session("YES")) { echo "ANON FAILED TO SEE OWN SESSION"; return; }
	
	// anon and hdu1 communicating
	if (!hduser_get_message($hdu1_name, "empty", 0)) { echo "HDU1 FAILED TO GET EMPTY ANON MESSAGE"; return; }
	if (!hduser_set_message($hdu1_name, "OK", "hdu1 message")) { echo "HDU1 FAILED TO SEND ANON MESSAGE"; return; }
	if (!anon_get_message("hdu1 message", 0)) { echo "ANON FAILED TO GET HDU1 MESSAGE"; return; }
	if (!anon_set_message("OK", "anon message")) { echo "ANON FAILED TO SEND HDU1 MESSAGE"; return; }
	if (!hduser_get_message($hdu1_name, "anon message", 1)) { echo "HDU1 FAILED TO GET ANON MESSAGE"; return; }
	
	// transfer session to hdu2 and hdu2 anon communication
	if (!transfer_session($hdu1_name, $hdu2_name, "OK")) { echo "HDU1 FAILED TO TRANSFER SESSION"; return; }
	if (!hdu_pop_queue($hdu2_name, "OK")) { echo "HDU2 FAILED TO GET FROM TRANSFER"; return; }
	if (!hduser_get_message($hdu2_name, "anon message", 1)) { echo "HDU2 FAILED TO GET ANON MESSAGE"; return; }
	if (!hduser_set_message($hdu2_name, "OK", "hdu2 message")) { echo "HDU2 FAILED TO SEND ANON MESSAGE"; return; }
	if (!anon_get_message("hdu2 message", 2)) { echo "ANON FAILED TO GET HDU2 MESSAGE"; return; }
	if (!anon_set_message("OK", "anon message2")) { echo "ANON FAILED TO SEND HDU2 MESSAGE"; return; }
	if (!hduser_get_message($hdu2_name, "anon message2", 3)) { echo "HDU2 FAILED TO GET ANON MESSAGE"; return; }
	
	// hdu1 and hdu2 communicating then transfer session
	if (!hduser_check_hdu_session($hdu1_name, $hdu2_name, "OK", "NO")) { echo "HDU1 FAILED TO CHECK EMPTY HDU SESSION"; return; }
	if (!hduser_set_hdu_session($hdu1_name, $hdu2_name, "OK")) { echo "HDU1 FAILED TO SET HDU SESSION"; return; }
	if (!hduser_check_hdu_session($hdu1_name, $hdu2_name, "OK", "NUMBER")) { echo "HDU1 FAILED TO CHECK HDU SESSION"; return; }
	if (!get_hda_message($hdu1_name, $hdu2_name, "empty", 0)) { echo "HDU1 FAILED TO GET EMPTY HDU MESSAGE"; return; }
	if (!set_hda_message($hdu1_name, $hdu2_name, "OK", "hdu1 hdu message")) { echo "HDU1 FAILED TO SET HDU MESSAGE"; return; }
	if (!hduser_check_hdu_session($hdu2_name, $hdu1_name, "OK", "NUMBER")) { echo "HDU2 FAILED TO CHECK HDU SESSION"; return; }
	if (!get_hda_message($hdu2_name, $hdu1_name, "hdu1 hdu message", 0)) { echo "HDU2 FAILED TO GET HDU1 MESSAGE"; return; }
	if (!set_hda_message($hdu2_name, $hdu1_name, "OK", "hdu2 hdu message")) { echo "HDU2 FAILED TO SET HDU MESSAGE"; return; }
	if (!get_hda_message($hdu1_name, $hdu2_name, "hdu2 hdu message", 1)) { echo "HDU1 FAILED TO GET HDU2 MESSAGE"; return; }
	if (!transfer_session($hdu2_name, $hdu1_name, "OK")) { echo "HDU2 FAILED TO TRANSFER SESSION"; return; }
	
	// anon and hdu1 communicating again
	if (!hdu_pop_queue($hdu1_name, "OK")) { echo "HDU1 FAILED TO GET FROM TRANSFER"; return; }
	if (!hduser_get_message($hdu1_name, "anon message2", 3)) { echo "HDU1 FAILED TO GET PREVIOUS ANON MESSAGE"; return; }
	if (!hduser_set_message($hdu1_name, "OK", "new hdu1 message")) { echo "HDU1 FAILED TO SEND NEW ANON MESSAGE"; return; }
	if (!anon_get_message("new hdu1 message", 4)) { echo "ANON FAILED TO GET NEW HDU1 MESSAGE"; return; }
	if (!anon_set_message("OK", "anon message3")) { echo "ANON FAILED TO SEND HDU1 MESSAGE"; return; }
	if (!hduser_get_message($hdu1_name, "anon message3", 5)) { echo "HDU1 FAILED TO GET NEW ANON MESSAGE"; return; }
	
	// anon ending session
	//if (!end_session_hduser1("OK")) { echo "HDU1 FAILED TO END SESSION"; return; }
	if (!anon_end_session("OK")) { echo "ANON FAILED TO END SESSION"; return; }
	if (!has_session_ended($hdu1_name, "OK", "Ended")) { echo "HDU1 FAILED TO DETECT END SESSION"; return; }
	
	echo "ALL TESTS PASS";
	// ------------------------TESTS END--------------------------------
	
	// Given an associative array, returns the URL with the array contents in json
	function makeURL($array) {
		$url = "https://localhost/HelpDesk/HDcontroller.php?obj={";
		foreach($array as $key => $value) {
			$url .= "\"" . $key . "\":\"" . $value . "\",";
		}
		$url = rtrim($url, " ,") . "}";
		return str_replace( " ", "%20", $url); // whitespaces to proper url
	}
	
	// Given a URL, sends an AJAX request with the current session_id in the session cookie
	function sendJSON($url) {
		// give proper cookie to send in JSON, close to allow ajax to access cookie
		$_COOKIE['PHPSESSID'] = session_id();
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
		session_write_close();
		
		// look at all the commands and inputs
		// echo str_replace("localhost/HelpDesk/HDcontroller.php?obj=", "", $url) . "</p>";
		
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_COOKIE, $strCookie);
		
		// Accept any server (peer) certificate
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
		
		$result = json_decode(curl_exec($curlSession), true);
		curl_close($curlSession);
		return $result;
	}
	
	function sendPostToAuth($username, $password) {
		// give proper cookie to send in JSON, close to allow ajax to access cookie
		$_COOKIE['PHPSESSID'] = session_id();
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
		session_write_close();
		
		$url = "https://localhost/HelpDesk/HDauth.php";
		$postFields = "username=" . urlencode($username) . "&password=" . urlencode($password);
		
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_COOKIE, $strCookie);
		
		// POST
		curl_setopt($curlSession, CURLOPT_POST, 2);
		curl_setopt($curlSession, CURLOPT_POSTFIELDS, $postFields);
		
		// Accept any server (peer) certificate
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
		
		$result = curl_exec($curlSession);
		curl_close($curlSession);
		return $result;
	}
	
	function hdu_login($hdu_name, $hdu_pass, $message) {
		// HDU2 valid authentication only
		if ($hdu_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		// all invalid authentication tried with hdu1
		} else {
			session_id($GLOBALS["hdu1_sid"]);
		}
		$result = sendPostToAuth($hdu_name, $hdu_pass);
		
		$expected = $result === $message;
		return ($expected ? true : false);
	}
	
	function anon_enter_queue($status) {
		session_id($GLOBALS["anon_sid"]);
		$json = array("mode"=>"setAnon","strUserId"=>$GLOBALS["anon_name"]);
		$result = sendJSON(makeURL($json));
		
		$expected = $result["status"] === $status;
		return ($expected ? true : false);
	}
	
	function anon_check_queue_num($status, $message) {
		session_id($GLOBALS["anon_sid"]);
		$json = array("mode"=>"num_queue");
		$result = sendJSON(makeURL($json));
	
		$expected = ($result["status"] === $status && $result["message"] == $message);
		return ($expected ? true : false);
	}
	
	function anon_check_session($message) {
		session_id($GLOBALS["anon_sid"]);
		$json = array("mode"=>"checkSessionAnon");
		$result = sendJSON(makeURL($json));
	
		$expected = ($result["status"] === "OK" && $result["message"] == $message);
		return ($expected ? true : false);
	}
	
	function hdu_pop_queue($hdu_name, $status) {
		if ($hdu_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} elseif ($hdu_name == $GLOBALS["hdu2_name"]){
			session_id($GLOBALS["hdu2_sid"]);
		}
		
		$json = array("mode"=>"getAnon");
		$result = sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function hduser_get_message($hdu_name, $message, $index) {
		if ($hdu_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} elseif ($hdu_name == $GLOBALS["hdu2_name"]){
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array("mode"=>"getMessageForHDUser");
		$result = sendJSON(makeURL($json));
		
		if ($message === "empty") {
			$expected = ($result["status"] === "OK" && empty($result["message"]));
		} else {
			$regex = "/" . $message . "/";
			$match = preg_match($regex, $result["message"][$index]);
			$expected = ($result["status"] === "OK" && $match);
		}
		return ($expected ? true : false);
	}
	
	function hduser_set_message($hdu_name, $status, $message) {
		if ($hdu_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array("mode"=>"setMessageHDUser","strMessage"=>$message);
		$result = sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function anon_get_message($message, $index) {
		session_id($GLOBALS["anon_sid"]);
		$json = array("mode"=>"getMessageForAnon");
		$result = sendJSON(makeURL($json));
		
		if ($message === "empty") {
			$expected = ($result["status"] === "OK" && empty($result["message"]));
		} else {
			$regex = "/" . $message . "/";
			$match = preg_match($regex, $result["message"][$index]);
			$expected = ($result["status"] === "OK" && $match);
		}
		return ($expected ? true : false);
	}
	
	function anon_set_message($status, $message) {
		session_id($GLOBALS["anon_sid"]);
		$json = array("mode"=>"setMessageAnon", "strMessage"=> $message);
		$result = sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function transfer_session($hdu1_name, $hdu2_name, $status) {
		if ($hdu1_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu1_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array("mode"=>"transferSession", "hdUser"=>$hdu2_name);
		$result = sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function hduser_check_hdu_session($hdu1_name, $hdu2_name, $status, $message) {
		if ($hdu1_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu1_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array("mode"=>"getPsid", "hdUser"=>$hdu2_name);
		$result = sendJSON(makeURL($json));
		
		// getPsid returns an incremented number if psid exists
		if ($message == "NUMBER") {
			$expected = ($result["status"] === $status && is_numeric(($result["message"])));
		} else {
			$expected = ($result["status"] === $status && $result["message"] == $message);
		}
		return ($expected ? true : false);
	}
	
	function hduser_set_hdu_session($hdu1_name, $hdu2_name, $status) {
		if ($hdu1_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu1_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array("mode"=>"insertNewPsid", "hdUser"=>$hdu2_name);
		$result = sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function get_hda_message($hdu1_name, $hdu2_name, $message, $index) {
		if ($hdu1_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu1_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		
		$json = array("mode"=>"getHdaMessagesForHdaUser");
		$result =  sendJSON(makeURL($json));
		
		if ($message === "empty") {
			$expected = ($result["status"] === "OK" && empty($result["message"]));
		} else {
			$regex = "/" . $message . "/";
			$match = preg_match($regex, $result["message"][$index]);
			$expected = ($result["status"] === "OK" && $match);
		}
		return ($expected ? true : false);
	}
	
	function set_hda_message($hdu1_name, $hdu2_name, $status, $message) {
		if ($hdu1_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} else if ($hdu1_name == $GLOBALS["hdu2_name"]) {
			session_id($GLOBALS["hdu2_sid"]);
		}
		
		$json = array("mode"=>"insertMessageForHda","strMessage"=>$message);
		$result =  sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function end_session_hduser1($status) {
		session_id($GLOBALS["hdu1_sid"]);
		$json = array ("mode"=>"endSessionForHDUser");
		$result =  sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function anon_end_session($status) {
		session_id($GLOBALS["anon_sid"]);
		$json = array ("mode"=>"endSessionForUser");
		$result =  sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status);
		return ($expected ? true : false);
	}
	
	function has_session_ended($hdu_name, $status, $message) {
		if ($hdu_name == $GLOBALS["hdu1_name"]) {
			session_id($GLOBALS["hdu1_sid"]);
		} elseif ($hdu_name == $GLOBALS["hdu2_name"]){
			session_id($GLOBALS["hdu2_sid"]);
		}
		$json = array ("mode"=>"hasSessionEnded");
		$result =  sendJSON(makeURL($json));
		
		$expected = ($result["status"] === $status && $result["message"] === $message);
		return ($expected ? true : false);
	}
	
	
?>