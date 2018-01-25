<?php
	require_once("HDconstants.php");
	
	function executeQueryi($query, $bind_param_array) {
		$conn = initializeDBi();
		if ($conn->connect_errno) {
			throw new Exception("Failed to connect to MySQL: (" 
				. $conn->connect_errno . ") " . $conn->connect_error . " Query: " . $query);
		}
		
		if(!($stmt = $conn->prepare($query))) {
			throw new Exception("Prepare failed: (" . $conn->errno . ") " 
				. $conn->error . " Query: " . $query);
		}
		
		if ($bind_param_array) {
			call_user_func_array(array($stmt, "bind_param"), $bind_param_array);
		}
		
		if (!$stmt->execute()) {
			throw new Exception("Execute failed: (" . $stmt->errno . ") " 
				. $stmt->error . " Query: " . $query);
		}
		
		$res = $stmt->get_result();
		$stmt->close();
		$conn->close();
		
		return $res;
	}
	
	function resetAttemptsIfPastTimeout($ip, $timeout) {
		$query = "UPDATE loginattempts SET failedattempts 
			= IF(lastLogin < DATE_SUB(now(), INTERVAL ? MINUTE), 0, failedattempts) 
			WHERE ip=?";
		$bind_param_array = array("is", &$timeout, &$ip);
		return executeQueryi($query, $bind_param_array);
	}
	
	function hasExceededMaxAttempts($ip, $max) {
		$query = "SELECT ip FROM loginattempts WHERE ip=? AND failedattempts >= ?";
		$bind_param_array = array("si", &$ip, &$max);
		return executeQueryi($query, $bind_param_array);	
	}
	
	function inFailedLogin($ip) {
		$query = "SELECT failedattempts,LastLogin FROM loginattempts WHERE ip=?";
		$bind_param_array = array("s", &$ip);
		return executeQueryi($query, $bind_param_array);
	}
	
	function insertFailedLogin($ip) {
		$query = "INSERT INTO loginattempts Values (?, 1, now())";
		$bind_param_array = array("s", &$ip);
		return executeQueryi($query, $bind_param_array);
	}
	
	function updateFailedLogin($ip) {
		$query = "UPDATE loginattempts SET failedattempts 
			= IF(lastLogin < DATE_SUB(now(), INTERVAL 5 MINUTE), 1, failedattempts + 1) 
			WHERE ip=?";
		$bind_param_array = array("s", &$ip);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getHDPasswordi($hdUser) {
		$query = "SELECT password FROM hdaccounts ha INNER JOIN hdpassword hp 
			ON ha.hda_id = hp.hda_id WHERE ha.username=?";
		$bind_param_array = array("s", &$hdUser);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setHDUseri($username) {
		$query = "INSERT INTO hdaccounts Values ('', ?)";
		$bind_param_array = array("s", &$username);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getQueueNumberi($ticket_id) {		
		$query = "SELECT ticket_id FROM queue WHERE ticket_id <=?";
		$bind_param_array = array("i", &$ticket_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function checkSessionForAnoni($session_id) {
		$query = "SELECT ticket_id, anon_name FROM session WHERE anon_session_id = ?";
		$bind_param_array = array("s", &$session_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function anonInQueue($session_id) {
		$query = "SELECT session_id FROM queue WHERE session_id = ?";
		$bind_param_array = array("s", &$session_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setAnonToQueuei($session_id, $strUserId) {
		$query = "INSERT INTO queue Values ('', ?, ?)";
		$bind_param_array = array("ss", &$session_id, &$strUserId);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getTicketIdi($session_id) {	
		$query = "SELECT * FROM queue WHERE session_id=?";
		$bind_param_array = array("s", &$session_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function deleteAnonFromQueuei($session_id) {
		$query = "DELETE FROM queue WHERE session_id=?";
		$bind_param_array = array("s", &$session_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	// If only first query gets executed, could have locked table forever
	function getAnonFromQueuei() {
		$conn = initializeDBi();
		
		$query = "LOCK TABLE queue WRITE";
		mysqli_query($conn, $query);
		
		$query = "SELECT anon_name, ticket_id, session_id FROM queue ORDER BY ticket_id LIMIT 1; ";
		$res = mysqli_query($conn, $query);
		
		$query = "DELETE FROM queue ORDER BY ticket_id LIMIT 1;";
		mysqli_query($conn, $query);
		
		$query = "UNLOCK TABLES";
		mysqli_query($conn, $query);
		
		$conn->close();
		return $res;
	}
	
	function setSessioni($ticket_id, $hdUser, $session_id, $strUserId) {
		$query = "INSERT INTO session Values (?, ?, ?, ?, now(), '')";
		$bind_param_array = array("ssss", &$ticket_id, &$hdUser, &$session_id, &$strUserId);
		return executeQueryi($query, $bind_param_array);
	}
	
	function endSessioni($ticket_id) {
		$query = "UPDATE session SET end=now() WHERE ticket_id=?";
		$bind_param_array = array("s", &$ticket_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function sessionEndedi($ticket_id) {
		$query = "SELECT ticket_id FROM session WHERE ticket_id=? and end!=''";
		$bind_param_array = array("s", &$ticket_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getSessionForHdUseri($hdUser) {
		$query = "SELECT anon_name, ticket_id FROM session WHERE HDUser=? AND end=''";
		$bind_param_array = array("s", &$hdUser);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setMessagei($ticket_id, $strUserId, $strMessage) {
		$query = "INSERT INTO messages Values ('', ?, ?, ?, now())";
		$bind_param_array = array("sss", &$ticket_id, &$strUserId, &$strMessage);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getMessagesFromSessioni($ticket_id) {	
		$query = "SELECT * FROM messages WHERE ticket_id=?";
		$bind_param_array = array("s", &$ticket_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function transferSessionToi($ticket_id, $hdUser) {
		$query = "UPDATE session SET HDUser=? WHERE ticket_id=?";
		$bind_param_array = array("ss", &$hdUser, &$ticket_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getOnlineListi() {
		$query = "SELECT username FROM hdaonline h INNER JOIN hdaccounts ha 
					ON h.hda_id=ha.hda_id WHERE online='1'";
		return executeQueryi($query, null);
	}
	
	function getOfflineListi() {	
		$query = "SELECT username FROM hdaonline h INNER JOIN hdaccounts ha 
					ON h.hda_id=ha.hda_id WHERE online='0'";
		return executeQueryi($query, null);
	}
	
	function getRegisteredListi() {	
		$query = "SELECT username FROM hdaccounts h INNER JOIN hdpassword hp 
					ON h.hda_id=hp.hda_id";
		return executeQueryi($query, null);
	}
	
	
	function hdUserExisti($hdUser) {
		$query = "SELECT username FROM hdaccounts WHERE username=?";
		$bind_param_array = array("s", &$hdUser);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getPsidHdaMessagei($HDUser1, $HDUser2) {
		$query = "SELECT ps_id FROM hda_session WHERE 
					(hdAccount1=? AND hdAccount2=?) OR
					(hdAccount1=? AND hdAccount2=?)";
		$bind_param_array = array("ssss", &$HDUser1, &$HDUser2, &$HDUser2, &$HDUser1);
		return executeQueryi($query, $bind_param_array);
	}
	
	function insertNewPsidi($HDUser1, $HDUser2) {
		$query = "INSERT INTO hda_session Values ('', ?, ?, '0', '0')";
		$bind_param_array = array("ss", &$HDUser1, &$HDUser2);
		return executeQueryi($query, $bind_param_array);
	}
	
	function checkHdaSessioni($HDUser) {
		$query = "SELECT ps_id, hdAccount1, hdAccount2 FROM hda_session WHERE 
					(hdAccount1=? AND hda1_need_update='1') OR
					(hdAccount2=? AND hda2_need_update='1')";
		$bind_param_array = array("ss", &$HDUser, &$HDUser);
		return executeQueryi($query, $bind_param_array);
	}
	
	function getMessagesFromHdai($ps_id) {
		$query = "SELECT Timestamp, Sender, Content FROM hda_message WHERE ps_id=?";
		$bind_param_array = array("s", &$ps_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setHdaSessionReceivedi($ps_id, $hdUser) {
		$query = "UPDATE hda_session
					SET hda1_need_update = IF(hdAccount1 = ?, 0, hda1_need_update),
						hda2_need_update = IF(hdAccount2 = ?, 0, hda2_need_update)
					WHERE ps_id=?";
		$bind_param_array = array("sss", &$hdUser, &$hdUser, &$ps_id);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setMessageForHdai($ps_id, $hdUser, $strMessage) {
		$query = "INSERT INTO hda_message Values ('', ?, ?, ?, now())";
		$bind_param_array = array("sss", &$ps_id, &$hdUser, &$strMessage);
		return executeQueryi($query, $bind_param_array);
	}
	
	function setHdaSessionUpdatedForOtheri($ps_id, $hdUser) {

		$query = "UPDATE hda_session
					SET hda2_need_update = IF(hdAccount1 = ?, 1, hda2_need_update),
						hda1_need_update = IF(hdAccount2 = ?, 1, hda1_need_update)
					WHERE ps_id=?";
		$bind_param_array = array("sss", &$hdUser, &$hdUser, &$ps_id);
		return executeQueryi($query, $bind_param_array);
	}
?>