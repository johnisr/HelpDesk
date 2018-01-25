<?php
	
	function createCSRFGuard($numBytes) {
		$token = createNonce($numBytes);
		$_SESSION["CSRFGuard"] = $token;
		return $token;
	}
	
	function validateCSRFGuard($token) {
		if (isset($_SESSION["CSRFGuard"])) {
			if($_SESSION["CSRFGuard"] === $token) {
				return true;
			}
		}
		return false;
	}
	
	function unsetCSRFGuard() {
		$_SESSION["CSRFGuard"] = " ";
		unset($_SESSION["CSRFGuard"]);
	}

	function createNonce($numBytes) {
		$nonce = openssl_random_pseudo_bytes($numBytes);
		return bin2hex($nonce);
	}
?>