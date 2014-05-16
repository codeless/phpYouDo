<?php

/**
 * phpYouDo (PYD) - Mini Webapp engine
 * @author Manuel H <more@codeless.at>
 * Creation date: 2013-03-28
 */

# Enable strict error reporting and the output of messages
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);

# Get a list of all PYD applications
$applications = getApplicationNames();

# Collect and partially validate user input
$input = getInput();

# If no application is set and only one app exists
if (!$input['application'] && sizeof($applications) == 1) {
	# Run first and only app
	$input['application'] = $applications[0];
}

if ($input['application']) {
	$appPath	= 'pyd_' . $input['application'];
	$tplPath	= $appPath . DIRECTORY_SEPARATOR . 'templates';
	$appConfigFile	= $appPath . DIRECTORY_SEPARATOR . 'pyd.ini';
	$appConfig	= (is_file($appConfigFile))
				? readConfigFile($appConfigFile)
				: null;
	$sessionName	= (isset($appConfig) &&
				isset($appConfig['sessionName']))
				? $appConfig['sessionName']
				: $input['application'];
	session_name($sessionName);
}

# Start session
session_start();
session_regenerate_id();

# If mode is passon, an application must be set
if (isset($input['mode']) && $input['mode'] == 'passon' &&
	$input['application'])
{
	passon($applications, $tplPath . DIRECTORY_SEPARATOR .
		'pyd_applications.php');

	$reports = getReportNames();
	passon($reports, $tplPath . DIRECTORY_SEPARATOR . 'pyd_reports.php');

	if (isset($input['report']) && $input['report'] &&
		in_array($input['report'], $reports))
	{
		processReport();
	}
}
else {
	# Compile document title
	if ($input['application']) {
		# Compile document title
		$documentTitle = ucwords($input['application']);
		if (isset($input['report'])) {
			$documentTitle .= ' &raquo; ' .
				ucwords($input['report']);
		}
	} else {
		# Set defaults
		$documentTitle = 'phpYouDo';
	}

	declareDefaultTemplates();

	# If an application was chosen
	if ($input['application']) {
		# Overwrite default templates

		# Get all possible default templates:
		$templates = array_keys($defaultTemplates);

		# Check if those default templates do exist
		# for the chosen application:
		foreach ($templates as $t) {
			$templateFile =  $appPath . '/templates/pyd_' .
				$t . '.html';
			if (is_file($templateFile)) {
				$defaultTemplates[$t] = file_get_contents($templateFile);
			}
		}
	}

	# Get main template
	$document = $defaultTemplates['main'];
	list($documentHeader, $documentFooter) = explode('%PYD_CONTENT%', $document);

	if ($input['application']) {
		# Compile paths to default CSS and JS files
		$defaultFiles = array(
			'css'	=> $appPath . '/pyd.css',
			'js'	=> $appPath . '/pyd.js'
		);

		# If there is a default CSS and/or JS file
		foreach ($defaultFiles as $type => $path) {
			# If file exists
			if (is_file($path)) {
				# Integrate them into the documentFooter
				if ($type == 'css') {
					# Inject stylesheet to header
					$documentHeader = preg_replace(
						'/(<\/head>)/i',
						'<link rel="stylesheet" href="' . $path . '?v=' . filemtime($path) . '">' . PHP_EOL . '\1',
						$documentHeader);
				}
				else if ($type == 'js') {
					# Add script to footer
					$documentFooter =
						'<script src="' . $path . '?v=' . filemtime($path) . '"></script>' . PHP_EOL .
						$documentFooter;
				}
			}
		}

		# Compile paths to styles and scripts
		$customStyles	= $tplPath . 'pyd_styles.html';
		$customScripts 	= $tplPath . 'pyd_scripts.html';

		# Merge custom or default styles and scripts
		if (is_file($customStyles)) {
			$defaultTemplates['styles'] = file_get_contents($customStyles);
		}
		if (is_file($customScripts)) {
			$defaultTemplates['scripts'] = file_get_contents($customScripts);
		}
		$documentHeader = str_replace(
			array('%PYD_STYLES%', '%PYD_SCRIPTS%'),
			array($defaultTemplates['styles'], $defaultTemplates['scripts']),
			$documentHeader);
	} else {
		# Merge default styles and scripts into header
		$documentHeader = str_replace(
			array('%PYD_STYLES%', '%PYD_SCRIPTS%'),
			array($defaultTemplates['styles'], $defaultTemplates['scripts']),
			$documentHeader);
	}

	# Output document header
	echo $documentHeader;
	unset($documentHeader);

	# Export application name
	if ($input['application']) {
		exportVariable('activeApplication', $input['application']);
	}

	# Attach applications as JSON data to document
	attachToDocument('applications', $applications);

	if ($input['application']) {
		$reports = getReportNames();
		attachToDocument('reports', $reports);

		if (isset($input['report']) && $input['report'] && in_array($input['report'], $reports)) {
			processReport();
		}
	}

	# Output
	echo $documentFooter;
}


/*

Function: getApplicationNames

A PYD-application is defined by a folder which starts
with the string "pyd_" (prefix). Everything after the
prefix is supposed to be the applications name.

*/
function getApplicationNames() {
	$applicationDirectories = glob('pyd_*', GLOB_ONLYDIR);
	$applications = preg_replace('/^pyd_/', '', $applicationDirectories);
	return $applications;
}


/*

Function: readConfigFile

*/
function readConfigFile($filename) {
	$config = null;

	# If file does exist
	if (file_exists($filename)) {
		$config = parse_ini_file($filename, true);
	}
	else {
		trigger_error(
			'The configuration file ' . $filename . ' does not exist.',
			E_USER_ERROR);
	}

	return $config;
}


/*

Function: getReportNames

*/
function getReportNames() {
	global $input, $appPath;

	# If no report or no language is given
		# Read in the application's defaults

	# Get a list of all possible reports
	$reportsDirectory = $appPath . '/reports';
	$reportFiles = glob($reportsDirectory . '/*.ini.php');
	$reports = preg_replace(
		'/' . preg_quote($reportsDirectory, '/') . '\/(.*)\.ini.php$/',
		'\1',
		$reportFiles);

	return $reports;
}


/*

Function: exportVariable

*/
function exportVariable($name, $value, $setUpTemplate=false) {
	# Attach data to document
	echo '<script type="text/javascript">',PHP_EOL,
		'var ',$name,' = ',json_encode($value),';',PHP_EOL,
		(($setUpTemplate) ? 'setUp("'.$name.'", '.$name.');' : null),PHP_EOL,
		'</script>',PHP_EOL,PHP_EOL;
}


/*

Function: attachToDocument

Parameters:

	$name -
	$data -
	$template -
	$affectedRows -

*/
function attachToDocument($name, $data, $template=null, $affectedRows=null) {
	global $defaultTemplates;

	# If there is no template
	if (!$template) {
		$template = (isset($defaultTemplates[$name]))
			? $defaultTemplates[$name]
			: $defaultTemplates['records'];
	}

	# Attach template to document
	echo '<script type="text/html" id="',$name,'Template">',PHP_EOL,
		$template,PHP_EOL,
		'</script>',PHP_EOL,PHP_EOL;

	# Export the number of affected rows before the item export,
	# since the later one calls the template which might need
	# to query the number of affected rows:
	if (isset($affectedRows)) {
		exportVariable($name . 'AffectedRows', $affectedRows);
	}

	exportVariable($name, $data, true);
}


/*

Function: getInput

*/
function getInput() {
	global $applications;
	return filter_input_array(
		INPUT_GET,
		array( 	'application' => array(
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => array(
					'regexp' => '/(' . implode('|', $applications) . ')/'
				)
			),
		 	'mode' => array(
				'filter' => FILTER_VALIDATE_REGEXP,
				'options' => array(
					'regexp' => '/(passon)/'
				)
			),
			'report' => FILTER_SANITIZE_STRING,
			'language' => FILTER_SANITIZE_STRING)
	);
}


/*

Function: processReport

*/
function processReport($report=null) {
	global $input, $appPath, $tplPath;

	$databases = array();

	# If no report or no language is given
		# Read in the application's defaults

	# Get a list of all possible reports
	$reportsDirectory = $appPath . '/reports';

	# Set current report
	if (!$report) {
		$report = $input['report'];
	}

	# Read report configuration
	$reportConfigurationFile = $reportsDirectory . '/' .
		$report . '.ini.php';
	$reportConfiguration = readConfigFile($reportConfigurationFile);

	# Extract databases which are used by the queries
	foreach ($reportConfiguration as $sectionName => $c) {
		# Load subreport?
		$matches = array();
		if (preg_match('/^report ([a-z0-9_]+)( .*)?/', $sectionName, $matches)) {
			$subreport = $matches[1];
			processReport($subreport);
			continue;	# continue with next section
		}

		$log		= (isset($c['log'])) ? (int) $c['log'] : 0;
		$logPrefix	= null;

		if ($log) {
			$logPrefix = $input['application'] . '/' . $report . '/' . $sectionName;
		}

		if ($log && $log == 1) {
			querylog('GET params: ' . implode(',', array_keys($_GET)), $logPrefix);
			querylog('POST params: ' . implode(',', array_keys($_POST)), $logPrefix);
		}

		$databaseConfigDir = $appPath . '/databases/';

		if (isset($c['inlineQuery']) || isset($c['sql'])) {
			$queryName = $sectionName;

			if (!isset($c['database'])) {
				$databaseFiles = glob($databaseConfigDir . '*.ini.php');

				if (isset($databaseFiles[0])) {
					$defaultDBConfig = basename($databaseFiles[0]);
					$databaseID = str_replace('.ini.php', '', $defaultDBConfig);
				}
				else {
					trigger_error('No database set to run query on',
						E_USER_ERROR);
				}
			}
			else {
				$databaseID = $c['database'];
			}
		}
		else {
			list($databaseID, $queryName) =
				explode('.', $c['query']);
		}

		# If not yet connected to database
		if (!isset($databases[$databaseID])) {
			# Read configuration
			$databaseConfigurationFile =
				$databaseConfigDir .
				$databaseID . '.ini.php';
			$databaseConfiguration = readConfigFile($databaseConfigurationFile);

			# Compile Data-Source-Name (DSN)
			if ($databaseConfiguration['type'] == 'sqlite') {
				$dbfile	= dirname(realpath($databaseConfigurationFile)) .
					DIRECTORY_SEPARATOR .
					$databaseConfiguration['database'];
				$dsn	= $databaseConfiguration['type'] .
						':' . $dbfile;
				$user	= $password = null;
			} else {
				$dsn = 	$databaseConfiguration['type'] .
					':dbname=' .
					$databaseConfiguration['database'] .
					';host=' .
					$databaseConfiguration['host'] .
					';charset=' .
					(isset($databaseConfiguration['charset'])
						? $databaseConfiguration['charset']
						: 'utf8');
				$user = $databaseConfiguration['username'];
				$password = $databaseConfiguration['password'];
			}

			# Try to connect to database
			try {
				$databases[$databaseID] = new PDO(
					$dsn,
					$user,
					$password);
			}
			catch (PDOException $e) {
				trigger_error('Connection to database failed: ' .
					$e->getMessage(),
					E_USER_ERROR);
			}

			# Run probable startup queries:
			if (isset($databaseConfiguration['startupQueries']) &&
				is_array($databaseConfiguration['startupQueries']))
			{
				foreach ($databaseConfiguration['startupQueries'] as $q) {
					$databases[$databaseID]->query($q);
				}
			}
		}

		if (isset($c['inlineQuery'])) {
			$query = $c['inlineQuery'];
		}
		else if (isset($c['sql'])) {
			$query = $c['sql'];
		}
		else {
			# Compile path to query
			$queryFile = $appPath . '/queries/' . $c['query'] . '.ini.php';

			# Read query
			$queryConfig = readConfigFile($queryFile);
			$query = $queryConfig['query'];
		}

		# Initialize parameters array
		$parameters = array();

		# Bind parameters from POST or GET; traditional method
		if (isset($c['inlineQuery']) || isset($c['query'])) {
			if (isset($c['queryParameters'])) {
				# Loop through parameters
				foreach ($c['queryParameters'] as $p) {
					# Get and validate parameter:
					$sources = array(INPUT_POST, INPUT_GET);
					foreach ($sources as $source) {
						$parameterValue = filter_input(
							$source,
							$p,
							FILTER_SANITIZE_STRING);

						# If value is set
						if ($parameterValue) {
							break;
						}
					}

					# Add parameter:
					$parameters[$p] = $parameterValue;
				}
			}

			# Bind parameters from SESSION
			if (isset($c['queryParametersFromSession'])) {
				# Loop through parameters
				foreach ($c['queryParametersFromSession'] as $p) {
					# Add parameter:
					$parameters[$p] = (isset($_SESSION[$p]))
						? $_SESSION[$p]
						: null;
				}
			}
		} else if (isset($c['sql'])) {	# New method
			# Collect parameters by using a regex:
			$matches = array();
			$hits = preg_match_all(
				'/(:|#|\$)([A-Za-z0-9_]+)\b(\*)?/',
				$c['sql'],
				$matches);

			if ($hits) {
				$sources		= $matches[1];
				$parametersToBind	= $matches[2];
				$obligatory		= $matches[3];
				$bindList		= array();

				foreach ($parametersToBind as $i => $paramName) {
					if ($sources[$i] == ':') {
						$method = 'get';
						$value = filter_input(
							INPUT_GET,
							$paramName,
							FILTER_SANITIZE_STRING);
					} else if ($sources[$i] == '#') {
						$method = 'post';
						$value = filter_input(
							INPUT_POST,
							$paramName,
							FILTER_SANITIZE_STRING);
					} else if ($sources[$i] == '$') {
						$method = 'session';
						$value = (isset($_SESSION[$paramName]))
							? $_SESSION[$paramName]
							: null;
					}

					# Compile ID of param
					$paramID = $method . '_' . $paramName;

					# Save parameter
					$parameters[$paramID] = $value;

					# Obligatory param?
					if ($obligatory[$i] == '*') {
						if (!isset($c['obligatoryParameters'])) {
							$c['obligatoryParameters'] = array();
						}

						$c['obligatoryParameters'][] = $paramID;
					}

					$bindList[] = ':' . $paramID;
				}

				# Make obligatory params unique
				if (isset($c['obligatoryParameters'])) {
					$c['obligatoryParameters'] = array_unique($c['obligatoryParameters']);
				}

				# Fix SQL
				$query = str_replace($matches[0], $bindList, $c['sql']);
			}
		}

		if ($log && $log == 1) {
			querylog('SQL params: ' . implode(',', array_keys($parameters)), $logPrefix);
			querylog('SQL param values: ' . implode(',', array_values($parameters)), $logPrefix);
		}

		if (isset($c['obligatoryParameters'])) {
			if ($log && $log == 1) { querylog('Obligatory params: ' . implode(',', $c['obligatoryParameters']), $logPrefix); }
			$conditionsMet = true;

			foreach ($c['obligatoryParameters'] as $p) {
				# Obligatory parameters can either
				# be used inside the query or only
				# inside the GET or POST data
				if (!isset($parameters[$p]) && !isset($_GET[$p]) && !isset($_POST[$p]))
				{
					$conditionsMet = false;
					break;
				}
			}

			if (!$conditionsMet) {
				if ($log && $log == 1) { querylog('Missing obligatory params...', $logPrefix); }
				continue; # continue with next query
			}
		} else {
			if ($log && $log == 1) { querylog('No obligatory params', $logPrefix); }
		}

		# Prepare query
		$statementHandle = $databases[$databaseID]->prepare($query);
		$statementHandle->setFetchMode(PDO::FETCH_OBJ);

		# Bind params and execute query;
		# measure duration when logging:
		if ($log) { $starttime = microtime(true); }
		$rc = $statementHandle->execute($parameters);
		if ($log) {
			$endtime	= microtime(true);
			$duration	= $endtime - $starttime;
			querylog('Seconds needed for binding params and executing query: ' . $duration, $logPrefix);
		}

		# If query could get executed successfully
		if ($rc) {
			$affectedRows 	= $statementHandle->rowCount();
			$results 	= array();

			# Collect results:
			while ($r = $statementHandle->fetch()) {
				$results[] = $r;
			}
		}
		else {
			$affectedRows 	= null;
			$results 	= $statementHandle->errorInfo();
		}

		# If rows should get stored in session
		if (isset($c['saveRowFieldsToSession']) &&
			is_array($c['saveRowFieldsToSession']))
		{
			# Loop through rows
			foreach ($c['saveRowFieldsToSession'] as
				$rowIndex => $fieldsString)
			{
				# Is row set?
				if (isset($results[$rowIndex])) {
					$fields = explode(',', $fieldsString);

					# Loop through fields and store them
					foreach ($fields as $f) {
						if (isset($results[$rowIndex]->$f)) {
							if ($log && $log == 1) { querylog('Saving ' . $f . ' to session', $logPrefix); }
							$_SESSION[$f] = $results[$rowIndex]->$f;
						} else {
							if ($log && $log == 1) {
								querylog('Cannot save field "' . $f .
									'" of row ' . $rowIndex .
									' to session: field not set', $logPrefix);
							}
						}
					}
				} else {
					if ($log && $log == 1) { querylog('Cannot save row with index ' . $rowIndex . ' to session: row not set', $logPrefix); }
				}
			}
		}

		# Compile path to PHP template
		$tplFile = $tplPath . DIRECTORY_SEPARATOR . (isset($c['template']) ? $c['template'] : $sectionName) . '.php';

		# When template is PHP script or mode is passon
		if (is_file($tplFile) || $input['mode'] == 'passon') {
			# Compile templatename
			passon($results, $tplFile, $affectedRows);
		}
		else {
			# Check for a template
			$template = null;
			if (isset($c['template'])) {
				$templateFile = $appPath . '/templates/' . $c['template'] . '.html';
			} else {
				$templateFile = $appPath . '/templates/' . $sectionName . '.html';
			}

			# Check for file existance
			if (is_file($templateFile)) {
				$template = file_get_contents($templateFile);
			}

			# Print template and results:
			attachToDocument(
				$queryName,
				$results,
				$template,
				$affectedRows);
		}
	}
}

function declareDefaultTemplates() {
	global $documentTitle;

	# Default templates
	$defaultTemplates = array();
	$defaultTemplates['applications'] =<<< EOT
<div class="row">
	<div class="large-12 columns">
		<%
			if (typeof activeApplication === "undefined") {
				%>
				<br>
				<img align="right" src="http://www.codeless.at/phpyoudo.png"/>
				<h1>Welcome to <b>phpYouDo</b></h1>
				<br>
				<%
			}
		%>
		<h2>Please choose an application:</h2>
		<br>
		<%
			_.each(items, function(i) {
				%>
					<div class="panel">
						<h3><%= i %></h3>
						<pre>Contents of readme.md</pre>
						<a href="?application=<%= i %>"
							class="button">
							Start <%= i %> &raquo;
						</a>
					</div>
				<%
			});
		%>
	</div>
</div>
EOT;
	$defaultTemplates['reports'] =<<< EOT
<div class="row">
	<div class="large-12 columns">
		<h2>Active application: <%= activeApplication %></h2>
		<h3>Please choose a report:</h3>
		<ul class="button-group">
			<%
				_.each(items, function(i) {
					%>
						<li class="pyd-report-<%= i %>">
							<a href="?application=<%= activeApplication %>&report=<%= i %>" class="active button">
								<%= i %>
							</a>
						</li>
					<%
				});
			%>
		</ul>
	</div>
</div>
EOT;
	$defaultTemplates['records'] =<<< EOT
<div class="row">
	<div class="large-12 columns">
		<table>
			<thead>
				<!-- Header columns -->
				<tr>
				<%
					var obj = items[0];
					for (var key in obj) {
						var val=obj[key];
						%>
						<th class="<%= key %>"><%= key %></th>
						<%
					}
				%>
				</tr>
			</thead>

			<!-- Data columns -->
			<tbody>
			<%
				_.each(items, function(i) {
					%>
						<tr>
					<%
					for (var key in i) {
						var val=i[key];
						%>
						<td class="<%= key %>"><%= val %></td>
						<%
					}
					%>
						</tr>
					<%
				});
			%>
			</tbody>
		</table>
	</div>
</div>
EOT;
	$defaultTemplates['main'] =<<< EOT
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<!-- Set the viewport width to device width for mobile -->
	<meta name="viewport" content="width=device-width" />
	<title>$documentTitle</title>
	%PYD_STYLES%
</head>
<body>
%PYD_SCRIPTS%
<script type="text/javascript">
	function setUp(name, data) {
		var template = $("#" + name + "Template").html();
		$("body").append("<div id='" + name + "'></div>");
		$("#" + name).html(_.template(template, { items: data }));
	}
</script>

%PYD_CONTENT%

</body>
</html>
EOT;
	$defaultTemplates['styles'] =<<< EOT
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/normalize/2.1.0/normalize.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/foundation/5.2.2/css/foundation.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js"></script>
EOT;
	$defaultTemplates['scripts'] =<<< EOT
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
EOT;

	# Globalize default templates
	$GLOBALS['defaultTemplates'] = $defaultTemplates;
}


/*

Function: passon

Parameters:

	$data -
	$templateFile -

*/
function passon($data, $tplFile, $affectedRows=null) {
	if (is_file($tplFile)) {
		include($tplFile);
	}
}


/**
 *
 */
function pydlog($msg, $prefix=null) {
	$prefix = 'PYD ' . $prefix . ' ';
	error_log($prefix . $msg);
}

function querylog($msg, $prefix=null) {
	pydlog($msg, $prefix);
}


# Creating HTML documents with the DOM classes:
# http://www.ultramegatech.com/2009/07/generating-xhtml-documents-using-domdocument-in-php/
# http://php.net/manual/en/class.domdocument.php
die();
