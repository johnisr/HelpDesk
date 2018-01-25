<body onload="init()" >
	<div name="chat" id="chat" class="Page" >
		<div class="bdr" name="chatIntro" id="chatIntro">
			<img name="chatImage" id="chatImage" src="res/hdesk.png" alt="helpdesk chat" >
			<p>
			<b>
			<p style=color:blue>Welcome to the Tech Deck Chat Room.</p>
			<p style=color:red>Closing chat ends the session.</p>
			
			</b>
			</p>
		</div>
		
		<!--display issues with non firefox browser without this-->
		<p style=color:white>.</p>
		<p style=color:white>.</p>
		<p style=color:white>.</p>
		<div id="chatroom" style="align:right" class="title2"></div>
		
		<div id="messageform" class="bdr">

			<p id="SendErrorMessage"></p>
			<p>Message: <input type="text" name="strMessage" id="strMessage" placeholder="Enter Message Here" onkeydown="enterButton(arguments[0] || window.event, this.id)""/></p>
			<p>
				<input type="button" class="box" name="strMessageButton" id="strMessageButton" value="Send Message" onclick="set_message();" />
				<input type="button" class="box" name="exitButton" value="Close Chat" id="exitButton" onclick="end_session()">
			</p>
		</div>
	</div>
</body>