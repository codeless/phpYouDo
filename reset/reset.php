<?php

/**
 * Resets the SQLite demodb before changes are pushed to 
 * the repository.
 */

$config = parse_ini_file('dbs.ini');
foreach ($config['dsn'] as $dsn) {
	# Set filerights
	$dbfile = str_replace('sqlite:', '', $dsn);
	chmod($dbfile, 0666);

	# Connect to db
	$db = new PDO($dsn);

	# Reset db
	$sql_file = basename($dsn) . '.sql';
	$sql = file_get_contents($sql_file);
	$db->exec($sql);

	echo 'Reseted ',basename($dsn),PHP_EOL;
}
