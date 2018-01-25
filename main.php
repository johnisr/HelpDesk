<?php
	session_start();
	
	// Not logged in, go to HDauth to log in
	if (empty($_SESSION['userid'])) {
		$auth = "HDauth.php";
		header( "Location: " . $auth);
		exit();
	}

?>

<!-- passing php variables to javascript-->
<script type="text/javascript">
	var userId = <?php echo json_encode($_SESSION['userid']) ?>;
</script>

<!DOCTYPE html>
<head> 
	<link rel="stylesheet" type="text/css" href="res/tdeck.css">
	<link href='http://fonts.googleapis.com/css?family=Berkshire+Swash' rel='stylesheet' type='text/css'>
	<script type="text/javascript" src="res/main.js"></script>
 	<title>Tech Deck</title>
</head>

<body onload="init()" onbeforeunload="close()">
	
	<div name = "header" id="header">
		<hr size="25" width="100%" color=black>
		<img src="res/help.jpeg" alt="helpdesk chat" style="width:45px;height:45px;border:0;float:left">
		<h2 class="thread">Tech Deck Help Desk</h2>
		<hr size="25" width="100%" color=black>
	</div>
	
	<div name="nav" id="nav">
		<ul>
			<li><a id="navhomepage" class ="nav" onclick="showPage('homepage')">Home</a></li>
			<li><a id="navchat" class ="nav" onclick="showPage('chat')">Chat</a></li>
			<li><a id="navmessages" class ="nav" onclick="showPage('messages')">Messages</a></li>
			<li><a id="navoptions" class ="nav" onclick="showPage('options')">User Options</a></li>
			<li style="float:right"><a id="navLogout" class ="nav" onclick="logout()">Logout</a></li>
		</ul>
	</div>
	
	<div name = "homepage" id = "homepage" class = "Page">
			<p class="bdrtext" id="homepageWelcome">Hello again, </p>
			<p class="bdrtext" id="queueErrorMessage"></p>
			<p>
				<input type="button" name="userButton" value="Begin Chat with Anonymous User" id="userButton" class="box" onclick="get_anon()">
			</p>
			<p></p>
			<p class="bdrtext" style="color:green">Your recent activity: </p>

	</div>
	
	<div name="chat" id="chat" class="Page" style="display:none;">

		<h2  class = "bdr">Tech Deck Chatroom</h2>
		<div id="chatroom_ticket_id"></div>
		<div id="chatroom"></div>
		<div >
			<!--User name needs to be printed here that is acquired from login.html -->
			<p id="SendErrorMessage"></p>
			<p class = "bdr">Message:<input type="text" name="strMessage" id="strMessage" placeholder="Enter Message Here" onkeydown="enterButton(arguments[0] || window.event, this.id)"/></p>
			<p>
				<input type="button" name="strMessageButton" id="strMessageButton" class="box" value="Send Message" onclick="set_message();" />
				<input type="button" name="exitButton" value="Close Chat" id="exitButton" class="box" onclick="end_session()">
			</p>
		</div>
		<p id="transferErrorMessage"></p>
		<hr width="385px" align=left>
		<p class="bdrtext">Transfer to: <input list="registeredUsersChat" name="transferHduser" id="transferHduser" onkeydown="enterButton(arguments[0] || window.event, this.id)" /></p>
		<datalist id="registeredUsersChat"></datalist>
		<input type="button" name="transferHduserButton" value="Transfer Session" class="box" id="transferHduserButton" onclick="transfer_session()">
		<hr width="385px" align=left>
		<p class="bdrtext" style="color:orange">Your status is: Logged In</p>
		
	</div>
	
	<div name = "messages" id = "messages" class = "Page" style="display:none;">
			<p class="bdrtext" style="color:red" id="homepageWelcome">Your status is: Logged In</p>

			<p class="bdrtext">If you would like to speak with another user enter their name and press the request chat button.</p>
			<p class="bdrtext" id="incoming_messages"></p>
			<div id="message_request">
				<p class="bdrtext" style="color:red" id = "messagesErrorMessage"></p>
				<p class="bdrtext">HD User Name: <input list="registeredUsersMessages" name="otherHDUser" id="otherHDUser" onkeydown="enterButton(arguments[0] || window.event, this.id)"></p>
				<datalist id="registeredUsersMessages"></datalist>
				<input type="button" name="otherHDUserButton" value="Request Chat" class="box" id="otherHDUserButton" onclick="get_private_session()" >
			</div>
			<div id="message_chatroom"></div>
			<div id="message_chat" style="display: none">
				<!--User name needs to be printed here that is acquired from login.html -->
				<p id="SendErrorMessage"></p>
				<p class="bdrtext">Message: <input type="text" name="strHduMessage" id="strHduMessage" onkeydown="enterButton(arguments[0] || window.event, this.id)"/></p>
				<input type="button" class="box" name="strHduMessageButton" id="strHduMessageButton" value="Send Message" onclick="set_hda_message()" />
				<input type="button" class="box" value="Request Another User" onclick="change_hduser_chat()" />
			</div>


	</div>
	
	<div name = "options" id = "options" class = "Page" style="display:none;">
		<p class="bdrtext" style="color:red" id="homepageWelcome">Your status is: Logged In</p>

		<p class="bdrtext">Your current email address is: </p>
		<form action="" method="post">
		<input type="hidden" name="csrfg" id="csrfg" value=
						<?php 	require_once("HDcsrfg.php");
								echo json_encode(createCSRFGuard(32));
						?> >
			<p class="bdrtext">New E-Mail Address: <input type="text" name="email" id="email"</p>
			<p>
				<input type="button" class="box" name="chatButton" value="Change E-Mail" id="eButton" onclick="">
			</p>
		</form>

	</div>
	
</body>
</html>