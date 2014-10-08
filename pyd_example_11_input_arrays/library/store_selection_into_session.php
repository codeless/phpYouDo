<?php

# To demonstrate the usage of Session-arrays in SQL-queries,
# the selection is stored into the session:

$_SESSION['details'] = (isset($_GET['details']))
	? filter_var_array($_GET['details'], FILTER_SANITIZE_STRING)
	: null;
