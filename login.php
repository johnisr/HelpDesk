<?php
	session_start();
?>

<script type="text/javascript">
	var errorMessage = 
		<?php 	// Passing variables to javascript to dynamically decide which functions to run
				if (isset($_SESSION['errorMessage'])) {
					echo json_encode($_SESSION['errorMessage']);
					unset($_SESSION['errorMessage']);
				} else { 
					echo "\"\"";
				} 
		?>;
				
</script>

<!DOCTYPE HTML>
<head>
	<link rel="stylesheet" type="text/css" href="res/tdeck.css">
	<link href='http://fonts.googleapis.com/css?family=Berkshire+Swash' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="res/login.js"></script>
	<title>Tech Deck</title>
</head>
<body onload="init()">
	<div name = "header" id="header">
		<hr size="25" width="100%" color=black>
		<img src="res/help.jpeg" alt="helpdesk chat" style="width:45px;height:45px;border:0;float:left">
		<h2 class="thread">Tech Deck Help Desk</h2>
		<hr size="25" width="100%" color=black>
	</div>
	
	<div name = "hdLogin" id="hdLogin" class="Page">
		<h2 class="bdrtext" style=font-size:24>Tech Deck Response Center</h2>
			<button onclick="document.getElementById('id01').style.display='block'" class="box2" style="width:auto;">Login</button>
		
		<p class="bdrtext"><a href="HDoauth.php" style="text-decoration:none">Login with EECS Account Here</a> </p>
		<p class="bdrtext"><a href="anon.php" style="text-decoration:none">Return to Tech Deck Homepage</a> </p>
		<div id="id01" class="modal">
		<form class="modal-content animate" action="HDauth.php" method="post" onsubmit="">
				<span class="imgcontainer">
					<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal"></span>
					<img src="res/help_desk_logo.png" alt="Avatar" class="avatar">
				</span>
				
				<div class="container">
				<p class="bdrtext" style=color:blue id="loginErrorMessage"></p>
					<label class="bdrtext"><b>Username</b></label>
					<input type="login" placeholder="Enter Username" name="username" id="username" required>
					
					<label class="bdrtext"><b>Password</b></label>
					<input type="password" placeholder="Enter Password" name="password" id="password"required>
					<input type="hidden" name="csrfg" id="csrfg" value=
						<?php 	require_once("HDcsrfg.php");
								echo json_encode(createCSRFGuard(32));
						?> >
					
					
					<button id="log" class="box3" type="submit" onclick="authenticate()" >Login</button>
				</div>
				<div class="container" style="background-color:#f1f1f1">
					<button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
		</form>
		</div>
		</div>
	</div>
	
	<script>
		// Get the modal
		var modal = document.getElementById('id01');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	</script>
</body>
</html>