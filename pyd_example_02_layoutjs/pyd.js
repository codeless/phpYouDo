$(function() {
	// Create layout
	$("body").layout({
		south__initHidden: true,
		east__minSize: 200,
		west__minSize: 200,
		west__maxSize: 600,
		center__minWidth: 300
	});

	// Run foundation
	$(document).foundation();
});
