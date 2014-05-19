<?php

# The query results have been passed as $data-array to this script;
# Append one column with some PHP-calculated stuff to the set:
foreach ($data as $d) {
	$d->stuff = $d->lorem * 10;
}

# Export data with default records template:
attachToDocument('stuff', $data);

# Export data with individual template:
$template = file_get_contents(
	'pyd_example_06_php_template/templates/indistuff.html');
attachToDocument('indistuff', $data, $template);
