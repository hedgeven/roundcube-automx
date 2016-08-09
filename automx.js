$(document).ready(function()
{
	//hide the server-select dropdown
	var serverSelect = $("#login-form label[for='rcmloginhost']");
	serverSelect.parents("tr").hide();
});
