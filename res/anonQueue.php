<body onload="init()" >
	<div name="queue" id="queue" class="Page">
		<h2 class="bdrtext">Please wait for a representative to become available. </h2>
		<img src="res/loading2.gif" alt="loading" style="width:130px;height:100px;border:0">
		<p class="bdrtext" id="queueNumberMessage"></p>
		<p>
			<input type="button" class="box" name="cancelButton" value="Cancel Request" id="cancelButton" onclick="exit_queue()">
		</p>
	</div>
</body>