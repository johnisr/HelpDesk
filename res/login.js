// Display any error message from previous login attempts
function init() {
	document.getElementById('id01').style.display='block';
	document.getElementById("loginErrorMessage").innerHTML = errorMessage;
}