<?php

if (isset($data[0])) {
	# Do whatever you like here:
	error_log($data[0]->input);
	$input = strip_tags($data[0]->input, '<strong><em>');
	error_log($input);

	# Save value to session
	$_SESSION['html'] = $input;
}
