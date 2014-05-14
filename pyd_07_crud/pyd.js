// This file will be automatically included by PYD
$(function() {
	$(document).foundation();

	// Add confirmation dialog
	$("a.confirm").click(function() {
		return confirm("Really " + $(this).attr("title") + "?");
	});
});
