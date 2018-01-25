<body onload="init()" >
	<div name="login" id="login" class="Page">
		<h2 class="bdrtext">Enter Guest Chat with a Teck Deck User</h2>
		
		<p class="bdrtext" style=font-size:15>Name: <input type="text"  name="strUserId" id="strUserId" placeholder="Enter Name Here" onkeydown="enterButton(arguments[0] || window.event, this.id)"</p>
		<p>
			<input type="button" class="box" name="strUserIdButton" value="Enter" id="strUserIdButton" onclick="enter_queue()">
		</p>
		<hr size="3" width="100%" color=black>
		<div>
			<p class="bdrtext">Tech Deck Users: Login Here</p>
			<input type="button" class="box" name="logger" value="Tech Deck User Login" id="logger" onclick="location.href='login.php';">
		</div>
	</div>
</body>