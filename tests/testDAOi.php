<?php
	require_once("../HDdao.php");
	require_once("../HDmodel.php");
	
		//$res = authenticate("hello", "world");
		//echo $res[0];
		//echo $res[1];
		
		//$res = getHDPasswordi("asdf");
		// $row = $res->fetch_assoc();
		
		//$res = setHDUseri("johnisr");
		
		//$res = getQueueNumberi(69);
		
		//$res = checkSessionForAnoni("4s5qth8u9j31mfmamd6gijske7");
		//$row = $res->fetch_assoc();
		
		//$res = setAnonToQueuei("123", "Hello");
		
		$res = getTicketIdi("1fuqvcjhgc95070pj88gd8lj64");
		//$row = $res->fetch_assoc();
	
		//$res = deleteAnonFromQueuei("899bi5j40h0r9dbe6t2o41u9q3");
		
		//$res = getAnonFromQueuei();
		//$row = $res->fetch_assoc();
		
		//$res = getOfflineListi();
		
		// $res = getRegisteredListi();
		
		
		// if ($res->num_rows > 0) {
			// foreach ($row as $key=>$value) {
				// echo "KEY: " . $key . " Value: " . $value . "</p>";
			// }
		// } else {
			// echo "No rows";
		// }
		// $temp = array();
		// while ($row = $res->fetch_assoc()) {
			// foreach ($row as $key=>$value) {
				// echo "KEY: " . $key . " Value: " . $value . "</p>";
				// $temp[$key] = $value;
			// }
		// }
		// foreach ($temp as $key=>$value) {
				// echo "KEY: " . $key . " Value: " . $value . "</p>";
			// }
		
		$res = getRegisteredUsers(1);
		echo $res[0];
		echo print_r($res[1]);
		$arr = $res[1];
		for ($i = 0; $i < 5; $i++) {
			echo $res[1][0][$i];
		}
		
		
		
		
		
		
		//echo $row["password"];
		//echo $res->num_rows;
		// echo $res->num_rows;

?>