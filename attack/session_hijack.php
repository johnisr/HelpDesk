<?php
	// NOTE: Change to appropriate values before running
	$anon_session_id = "b8qkvs19a0iqaboiqfpoqb7ms1";
	$web_server_url = "192.168.0.205/HelpDesk/HDcontroller.php";
	
	session_start();
	
	get_message($anon_session_id, $web_server_url);
	
	function get_message($session_id, $web_server_url) {
		session_id($session_id);
		$json = array("mode"=>"getMessageForAnon");
		$result = sendJSON(makeURL($web_server_url, $json));
		
		foreach ($result["message"] as $message) {
			echo $message . "<p>";
		}
	}
	
	function makeURL($web_server_url, $json) {
		$url = $web_server_url . "?obj={";
		foreach($json as $key => $value) {
			$url .= "\"" . $key . "\":\"" . $value . "\",";
		}
		$url = rtrim($url, " ,") . "}";
		return str_replace( " ", "%20", $url); // whitespaces to proper url
	}
	
	function sendJSON($url) {
		// give proper cookie to send in JSON, close to allow ajax to access cookie
		$_COOKIE['PHPSESSID'] = session_id();
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
		session_write_close();
		
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_COOKIE, $strCookie);
		$result = json_decode(curl_exec($curlSession), true);
		return $result;
	}

?>