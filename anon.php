<?php
	session_start();
?>

<script type="text/javascript">
	var bookmark = 
		<?php 	// Passing variables to javascript to dynamically decide which functions to run
				if (isset($_SESSION['bookmark'])) { 
					echo json_encode($_SESSION['bookmark']);
				} else { 
					echo "\"login\"";
				} 
		?>;
	var uploadMessage = 
		<?php 	if (isset($_SESSION['uploadMessage'])) { 
					echo json_encode($_SESSION['uploadMessage']);
					unset($_SESSION['uploadMessage']);
				} else { 
					echo "\"\"";
				} 
		?>;
</script>

<!DOCTYPE HTML>
<head>
	<link rel="stylesheet" type="text/css" href="res/tdeck.css">
	<link href='http://fonts.googleapis.com/css?family=Berkshire+Swash' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="res/anon.js"></script>
	<title>Tech Deck</title>
</head>
<body onload="init()" >
	
	<div name = "header" id="header">
		<hr size="25" width="100%" color=black>
		<img src="res/help.jpeg" alt="helpdesk chat" style="width:45px;height:45px;border:0;float:left">

		<h2 class="thread">Tech Deck Help Desk</h2>
		<hr size="25" width="100%" color=black>
	</div>
	
	<div name="search" id="search" class="nav">
		<script>
		  (function() {
			var cx = '006230290751040091147:_3tl1oal1c4';
			var gcse = document.createElement('script');
			gcse.type = 'text/javascript';
			gcse.async = true;
			gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
			var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(gcse, s);
		  })();
		</script>
		<gcse:search></gcse:search>
	</div>
	
	<div name="upload" id="upload" class="aside">
		<p id="uploadMessage"></p>
		
		<form enctype="multipart/form-data" action="HDupload.php" method="POST">
			<input type="hidden" class="box" name="MAX_FILE_SIZE" value="500000" />
			<p class="bdrtext" style=font-size:20>File Upload:</p>
			<input name="userfile" type="file" class="box"/>
			<input type="hidden" name="csrfg" id="csrfg" value=
						<?php 	require_once("HDcsrfg.php");
								echo json_encode(createCSRFGuard(32));
						?> >
			<p><input type="submit" class="box" value="Send File" /></p>
		</form>
	</div>
	
	<?php
		// Dynamically decide content at runtime based on $_SESSION variable bookmark
		$array = array("login" =>"anonLogin.php", "queue"=>"anonQueue.php", "chat"=>"anonChat.php", "exit"=>"anonExit.php");
		$display = "res/";
		if (isset($_SESSION['bookmark'])) {	
			$display .= $array[$_SESSION['bookmark']];
		} else {
			$display .= $array["login"];
		}
		include "$display";
	?>
	
</body>
</html>