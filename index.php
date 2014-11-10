<?php

/**
 * phpYouDo (PYD) - Mini Webapp engine
 * @author Manuel H <more@codeless.at>
 * @license http://creativecommons.org/licenses/by/4.0/
 *	Creative Commons Attribution 4.0 International License
 * Creation date: 2013-03-28
 */

# Enable strict error reporting and the output of messages
error_reporting(E_ALL | E_STRICT);
ini_set("display_errors", 1);

# Get a list of all PYD applications
$applications = getApplications();

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

	if (isset($appConfig) && isset($appConfig['mode'])) {
		$input['mode'] = $appConfig['mode'];
	}

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
if (	isset($input['mode']) &&
	$input['mode'] == 'passon' &&
	$input['application'])
{
	passon($applications, $tplPath . DIRECTORY_SEPARATOR .
		'pyd_applications.php');

	$reports = getReportNames();
	passon($reports, $tplPath . DIRECTORY_SEPARATOR . 'pyd_reports.php');

	if (	isset($input['report']) &&
		$input['report'] &&
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
				$defaultTemplates[$t] = file_get_contents(
					$templateFile);
			}
		}
	}

	# Get main template
	$document = $defaultTemplates['main'];
	list($documentHeader, $documentFooter) = explode(
		'%PYD_CONTENT%',
		$document);

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
						'<link rel="stylesheet" href="' .
						$path . '?v=' .
						filemtime($path) . '">' .
						PHP_EOL . '\1',
						$documentHeader);
				}
				else if ($type == 'js') {
					# Add script to footer
					$documentFooter =
						'<script src="' . $path .
						'?v=' . filemtime($path) .
						'"></script>' . PHP_EOL .
						$documentFooter;
				}
			}
		}

		# Compile paths to styles and scripts
		$customStyles	= $tplPath . 'pyd_styles.html';
		$customScripts 	= $tplPath . 'pyd_scripts.html';

		# Merge custom or default styles and scripts
		if (is_file($customStyles)) {
			$defaultTemplates['styles'] = file_get_contents(
				$customStyles);
		}
		if (is_file($customScripts)) {
			$defaultTemplates['scripts'] = file_get_contents(
				$customScripts);
		}
		$documentHeader = str_replace(
			array('%PYD_STYLES%', '%PYD_SCRIPTS%'),
			array($defaultTemplates['styles'],
				$defaultTemplates['scripts']),
			$documentHeader);
	} else {
		# Merge default styles and scripts into header
		$documentHeader = str_replace(
			array('%PYD_STYLES%', '%PYD_SCRIPTS%'),
			array($defaultTemplates['styles'],
				$defaultTemplates['scripts']),
			$documentHeader);
	}

	# Output document header
	echo $documentHeader;
	unset($documentHeader);

	# Export application name
	if ($input['application']) {
		exportVariable('activeApplication', $input['application']);

		# Attach applications as JSON data to document
		if (sizeof($applications) > 1) {
			attachToDocument(
				'applicationsQuicklaunch',
				$applications);
		}
	} else {
		# No application has been chosen yet;
		# get applications details and export them
		$applicationsDetails = getApplicationsDetails();
		attachToDocument('applicationsOverview', $applicationsDetails);
	}

	if ($input['application']) {
		# Get reports of current application
		$reports = getReportNames();

		# Validate selected report
		$activeReport = (isset($input['report']) &&
			$input['report'] &&
			in_array($input['report'], $reports))
			? $input['report']
			: null;
		exportVariable('activeReport', $activeReport);

		attachToDocument('reports', $reports);

		if ($activeReport) {
			processReport();
		}
	}

	# Output
	echo $documentFooter;
}


/**
 * A PYD-application is defined by a folder which starts
 * with the string "pyd_" (prefix). Everything after the
 * prefix is supposed to be the applications name.
 *
 * @return array Names of applications
 */
function getApplications()
{
	$applicationDirectories = glob('pyd_*', GLOB_ONLYDIR);
	$applications = preg_replace('/^pyd_/', '', $applicationDirectories);
	return $applications;
}


/**
 * @return table Application names as key, application description as value
 */
function getApplicationsDetails()
{
	global $applications;

	$details = array();

	foreach ($applications as $a) {
		# Set default description
		$desc = 'No description available';

		# Check for a README-file in the app's directory:
		$readmes = glob('pyd_' . $a . DIRECTORY_SEPARATOR . 'README*');

		# Extract app description
		if ($readmes && isset($readmes[0])) {
			$desc = file_get_contents(
				$readmes[0],	# file
				false,		# use_include_path
				null,		# context
				0,		# offset
				250);		# maxlen
		}

		$details[$a] = $desc;
	}

	return $details;
}


/**
 * @return table Configuration
 */
function readConfigFile($filename)
{
	$config = null;

	# If file does exist
	if (file_exists($filename)) {
		$config = parse_ini_file($filename, true);
	}
	else {
		trigger_error(
			'The configuration file ' . $filename .
				' does not exist.', E_USER_ERROR);
	}

	return $config;
}


function getReportNames()
{
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


/**
 * @param string $name
 * @param mixed $value
 * @param boolean $setUpTemplate
 */
function exportVariable($name, $value, $setUpTemplate=false)
{
	# Attach data to document
	echo '<script type="text/javascript">',PHP_EOL,
		'var ',$name,' = ',json_encode($value),';',PHP_EOL,
		(($setUpTemplate) ? 'setUp("'.$name.'", '.$name.');' : null),
		PHP_EOL,'</script>',PHP_EOL,PHP_EOL;
}


/**
 * @param string $name
 * @param array $data
 * @param string|null $template
 * @param int|null $affectedRows
 */
function attachToDocument($name, $data, $template=null, $affectedRows=null)
{
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


/**
 * @return table Filtered user input from the GET-method or, if running
 *	in CLI mode, from the commandline parameters.
 */
function getInput()
{
	global $applications;
	$input = array(
		'application'	=> null,
		'mode'		=> null,
		'report'	=> null,
		'language'	=> null);

	if (PHP_SAPI == 'cli') {
		$options = null;
		$longopts = array('application::', 'mode::',
			'report::', 'language::');
		$input = array_merge($input, getopt($options, $longopts));
	} else {
		$input = filter_input_array(
			INPUT_GET,
			array( 	'application' => array(
					'filter' => FILTER_VALIDATE_REGEXP,
					'options' => array(
						'regexp' => '/(' . implode('|',
							$applications) . ')/'
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

	return $input;
}


/**
 * @param string $report Name of the report to execute
 */
function processReport($report=null)
{
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

	# Loop through the sections and initialize empty subreports;
	# otherwise they would get ignored because of the usage of
	# next():
	foreach ($reportConfiguration as $sectionName => $c) {
		if (!$c) {
			# Update
			$reportConfiguration[$sectionName]['dummy'] = 1;
		}
	}

	# TODO: At this point, the section names should be checked.
	# If they interfere with the PYD internals, the results
	# are unpredictable.

	# Extract databases which are used by the queries
	$i = 0;
	$c = reset($reportConfiguration);
	do {
		++$i;
		$sectionName = key($reportConfiguration);

		# Fix section name
		if (	isset($c['_PYD_REAL_SECTION_NAME_']) &&
			is_int($sectionName))
		{
			$sectionName = $c['_PYD_REAL_SECTION_NAME_'];
		}

		$log		= (isset($c['log'])) ? (int) $c['log'] : 0;
		$logPrefix	= ($log)
			? $logPrefix = $input['application'] .
				DIRECTORY_SEPARATOR .
				$report .
				DIRECTORY_SEPARATOR .
				$sectionName
			: null;

		# The section name can hold parameters,
		# which are replaced with the actual value:
		$originalSectionName = $sectionName;
		$vars = extractVariables($sectionName, ':');
		$sectionName = str_replace(
			array_keys($vars[0]),
			array_values($vars[0]),
			$vars[1]
		);

		# Pre-evaluate an expression?
		if (isset($c['pre'])) {
			if (	!parseExpression($c['pre'],
				$log,
				$logPrefix))
			{
				if ($log && $log == 1) {
					querylog('Pre-test not passed',
						$logPrefix);
				}
				continue;
			}
		}

		# Repeat section?
		if (isset($c['till'])) {
			# Run section another time (after the current run)?
			if (parseExpression($c['till'], $log, $logPrefix)) {
				# Then inject another section into
				# the report:
				$newSection = array($c);
				$newSection[0]['_PYD_REAL_SECTION_NAME_'] =
					$originalSectionName;
				array_splice(
					$reportConfiguration,
					$i,
					0,
					$newSection
				);

				# Because the array_splice operation
				# positioned the array pointer at the
				# first position, the pointer has to
				# get re-positioned to the current element:
				for ($j = $i; $j > 1; $j--) {
					next($reportConfiguration);
				}
			}
		}

		# Load subreport?
		$matches = array();
		if (preg_match('/^report ([a-z0-9_]+)( .*)?/',
			$sectionName,
			$matches))
		{
			$subreport = $matches[1];
			processReport($subreport);
			continue;	# continue with next section
		}

		if ($log && $log == 1) {
			querylog('GET params: ' . implode(',',
				array_keys($_GET)), $logPrefix);
			querylog('POST params: ' . implode(',',
				array_keys($_POST)), $logPrefix);
		}

		$databaseConfigDir = $appPath . '/databases/';

		if (isset($c['inlineQuery']) || isset($c['sql'])) {
			$queryName = $sectionName;

			if (!isset($c['database'])) {
				$databaseFiles = glob($databaseConfigDir .
					'*.ini.php');

				if (isset($databaseFiles[0])) {
					$defaultDBConfig = basename(
						$databaseFiles[0]);
					$databaseID = str_replace(
						'.ini.php',
						'',
						$defaultDBConfig);
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
		else if (isset($c['query'])) {
			list($databaseID, $queryName) =
				explode('.', $c['query']);
		} else {
			continue;
		}

		# If not yet connected to database
		if (!isset($databases[$databaseID])) {
			# Read configuration
			$databaseConfigurationFile =
				$databaseConfigDir .
				$databaseID . '.ini.php';
			$databaseConfiguration = readConfigFile(
				$databaseConfigurationFile);

			# Compile Data-Source-Name (DSN)
			if (isset($databaseConfiguration['dsn'])) {
				$dsn	= $databaseConfiguration['dsn'];
				$user	= $databaseConfiguration['username'];
				$password = $databaseConfiguration['password'];
			} else if ($databaseConfiguration['type'] == 'sqlite') {
				$dbfile	= dirname(realpath(
					$databaseConfigurationFile)) .
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
			$queryFile = $appPath . '/queries/' . $c['query'] .
				'.ini.php';

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
							FILTER_SANITIZE_STRING
						);

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
				foreach ($c['queryParametersFromSession'] as $p)
				{
					# Add parameter:
					$parameters[$p] = (isset($_SESSION[$p]))
						? $_SESSION[$p]
						: null;
				}
			}
		} else if (isset($c['sql'])) {	# New method
			list(	$parameters,
				$query,
				$c['obligatoryParameters']) =
				extractVariables($c['sql']);
		}

		if ($log && $log == 1) {
			querylog('SQL params: ' . implode(',',
				array_keys($parameters)), $logPrefix);
			querylog('SQL param values: ' .
				json_encode($parameters), $logPrefix);
		}

		if (isset($c['obligatoryParameters'])) {
			if ($log && $log == 1) {
				querylog('Obligatory params: ' .
					implode(',',
					$c['obligatoryParameters']),
					$logPrefix);
			}
			$conditionsMet = true;

			foreach ($c['obligatoryParameters'] as $p) {
				# Obligatory parameters can either
				# be used inside the query or only
				# inside the GET or POST data
				if (	!isset($parameters[$p]) &&
					!isset($_GET[$p]) &&
					!isset($_POST[$p]))
				{
					$conditionsMet = false;
					break;
				}
			}

			if (!$conditionsMet) {
				if ($log && $log == 1) {
					querylog('Missing obligatory params...',
						$logPrefix);
				}

				if (isset($c['till'])) {
					$msg = 'phpYouDo exited because of probable infinite loop in section ' . $sectionName . ': preconditions not met';
					querylog($msg);
					die($msg);
				}

				continue; # continue with next query
			}
		} else {
			if ($log && $log == 1) {
				querylog('No obligatory params',
					$logPrefix);
			}
		}

		# Prepare query
		$statementHandle = $databases[$databaseID]
			->prepare($query);

		# Catch errors
		if (!$statementHandle) {
			trigger_error('Error in SQL statement: ' . $query);
			continue;
		}

		$statementHandle->setFetchMode(PDO::FETCH_OBJ);

		# Bind params and execute query;
		# measure duration when logging:
		if ($log) { $starttime = microtime(true); }
		$rc = $statementHandle->execute($parameters);
		if ($log) {
			$endtime	= microtime(true);
			$duration	= $endtime - $starttime;
			querylog('Seconds needed for binding params and ' .
				'executing query: ' . $duration, $logPrefix);
		}

		# If query could get executed successfully
		if ($rc) {
			$affectedRows 	= $statementHandle->rowCount();
			$lastInsertId	= $databases[$databaseID]->lastInsertId();
			$results 	= array();

			# Collect results:
			while ($r = $statementHandle->fetch()) {
				$results[] = $r;
			}
		}
		else {
			$affectedRows 	= null;
			$lastInsertId	= null;
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
		$tplFile = $tplPath . DIRECTORY_SEPARATOR .
			(isset($c['template'])
			? $c['template'] : $sectionName) . '.php';

		# When template is PHP script or mode is passon
		if (is_file($tplFile) || $input['mode'] == 'passon') {
			# Compile templatename
			passon($results, $tplFile, $affectedRows, $lastInsertId);
		}
		else {
			# Check for a template
			$template = null;
			if (isset($c['template'])) {
				$templateFile = $appPath . '/templates/' .
					$c['template'] . '.html';
			} else {
				$templateFile = $appPath . '/templates/' .
					$sectionName . '.html';
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
	} while ($c = next($reportConfiguration));
}

function declareDefaultTemplates()
{
	global $documentTitle;

	# Default templates
	$defaultTemplates = array();
	$defaultTemplates['applicationsOverview'] =<<< EOT
<div class="row">
	<div class="large-12 columns">
		<br>
		<img align="right" src="http://www.codeless.at/phpyoudo.png"/>
		<h1>Welcome to <b><i>phpYouDo</i></b></h1>
		<br>
		<h2>Please choose an application:</h2>
		<br>
		<%
		if (_.size(items) < 10) {
			_.each(items, function(description, name) {
				%>
					<div class="panel">
						<h3><%= name %></h3>
						<pre><%= description %></pre>
						<br>
						<a href="?application=<%=name%>"
							class="button">
							Start <%= name %>
							&raquo;
						</a>
					</div>
				<%
			});
		} else {
			%><div class="row"><%
			_.each(items, function(description, name) {
				%>
				<div class="large-3 columns">
					<div class="panel">
					<span data-tooltip class="has-tip"
						title="<%= description %>"
						><%= name %></span>
					<br>
					<br>
					<a href="?application=<%= name %>"
						class="small button">
						Start &raquo;
					</a>
					</div>
				</div>
				<%
			});
			%></div><%
		}
		%>
	</div>
</div>
EOT;
	$defaultTemplates['applicationsQuicklaunch'] =<<< EOT
<% if (items.length == 1) return; %>
<div class="row">
	<div class="large-12 columns">
		<a class="tiny button" href="?">&laquo; Return to the overview</a>
		<a href="#" data-dropdown="applications-dropdown"
			class="tiny secondary button dropdown">Choose an application</a><br>
		<ul id="applications-dropdown" data-dropdown-content class="f-dropdown">
			<%
				_.each(items, function(i) {%>
					<li><a href="?application=<%= i %>"><%= i %></a></li><%
				});
			%>
		</ul>
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
<script src="//cdnjs.cloudflare.com/ajax/libs/foundation/5.2.2/js/foundation.min.js"></script>
<script type="text/javascript">
	$(function() { $(document).foundation(); });
</script>
EOT;

	# Globalize default templates
	$GLOBALS['defaultTemplates'] = $defaultTemplates;
}


function passon($data, $tplFile, $affectedRows=null, $lastInsertId=null)
{
	if (is_file($tplFile)) {
		include($tplFile);
	}
}


function pydlog($msg, $prefix=null)
{
	$prefix = 'PYD ' . $prefix . ' ';
	error_log($prefix . $msg);
}

function querylog($msg, $prefix=null)
{
	pydlog($msg, $prefix);
}


/**
 * Parses the passed expression and replaces references to GET, POST and
 * SESSION variables with their actual values.
 *
 * @param string $expr The expression to parse
 *
 * @return string The expression with the integrated variable values
 */
function parseExpression($expr, $log, $logPrefix)
{
	list($variables, $fixedExpr) = extractVariables($expr, ':');

	# Convert expression to PHP code by injecting the values
	$phpCode = str_replace(
		array_keys($variables),
		array_values($variables),
		$fixedExpr);

	# And some syntax:
	$phpCode = 'return (' . $phpCode . ') ? 1 : 0;';

	# Log code
	if ($log && $log == 1) {
		querylog('Pre-testcode to evaluate: ' . $phpCode,
			$logPrefix);
	}

	return (eval($phpCode));
}


/**
 * Parses the passed expression and extracts references to GET, POST and
 * SESSION variables.
 *
 * @param string $expr The expression to parse
 * @param string $varPrefix The prefix to attach to extracted variable
 *	names; defaults to null
 *
 * @return array The first array-entry holds a table with the variable's
 *	name as key and its value (or values, in case of an array),
 *	while the second array-entry holds the expression in a fixed syntax;
 *	that is, the original variable definitions have been replaced with
 *	internal ones.
 *	The third array-entry holds an array of all obligatory params.
 */
function extractVariables($expr, $varPrefix=null)
{
	$fixedExpr = $expr;
	$matches = $vars = $obligatoryParams = array();
	$hits = preg_match_all(
		'/(:|#|\$)([A-Za-z0-9_]+)\b\[?([a-zA-Z_]+)?\]?(\*)?(!)?/',
		$expr,
		$matches);

	if ($hits) {
		# Loop through matches and get values
		$sources		= $matches[1];
		$parametersToBind	= $matches[2];
		$filters		= $matches[3];
		$obligatory		= $matches[4];
		$instant_bind		= $matches[5];
		$bindList		= array();

		foreach ($parametersToBind as $i => $paramName) {
			# Set filter
			$filter = (!$filters[$i])
				? FILTER_SANITIZE_STRING
				: constant($filters[$i]);

			$method = $value = null;

			if ($sources[$i] == ':') {
				$method = 'get';
				$value = filter_input(
					INPUT_GET,
					$paramName,
					$filter);
			} else if ($sources[$i] == '#') {
				$method = 'post';
				$value = filter_input(
					INPUT_POST,
					$paramName,
					$filter);
			} else if ($sources[$i] == '$') {
				$method = 'session';
				$value = (isset($_SESSION[$paramName]))
					? $_SESSION[$paramName]
					: null;
			}

			if (!$value && PHP_SAPI == 'cli') {
				$method = 'cli';
				$cliparam = getopt(
					null,
					array($paramName . '::')
				);

				$value = ($cliparam &&
					isset($cliparam[$paramName]))
					? $cliparam[$paramName]
					: null;
			}

			# If $value is empty and method is one of
			# get/post/session, check if input is an array:
			if (	(!$value || is_array($value)) && in_array(
					$method,
					array('get', 'post', 'session')))
			{
				# Check for an array
				$realParam = '$_' . strtoupper($method) .
					'["' . $paramName . '"]';
				$check = 'return (isset(' . $realParam .
					') && is_array(' . $realParam .
					') ? 1 : 0);';
				if (eval($check)) {
					# Get input array
					if ($method == 'get') {
						$value = filter_var_array(
							$_GET[$paramName],
							$filter);
					} else if ($method == 'post') {
						$value = filter_var_array(
							$_POST[$paramName],
							$filter);
					} else if ($method == 'session') {
						$value = filter_var_array(
							$_SESSION[$paramName],
							$filter);
					}

					# If the array has more than one
					# values, inject parameters into
					# flat array:
					$countValues = sizeof($value);
					if ($countValues > 1) {
						for (	$j = 1;
							$j < $countValues;
							$j++)
						{
# Compile ID of param
$paramID = $method . '_' . $paramName . '_PYD_ARRAY_' . $j;

# Save parameter
$vars[$varPrefix . $paramID] = $value[$j];

# Adapt bind list
if (!isset($addToBindList)) {
	$addToBindList = array();
}
$addToBindList[] = ':' . $paramID;

# Adapt matches
if (!isset($addToMatches)) {
	$addToMatches = array();
}
$addToMatches[] = ':' . $paramName . '_PYD_ARRAY_' . $j;

# Extend SQL
$expr = str_replace(
	$matches[0][$i],
	$matches[0][$i] . ',' . ':' . $paramName . '_PYD_ARRAY_' . $j,
	$expr);
						}
					}
/*
echo '<pre>';
print_r($bindList);
print_r($matches[0]);
die($expr);
*/

					# Set first value:
					$value = $value[0];
				}
			}

			# Compile ID of param
			$paramID = $method . '_' . $paramName;

			# instant_bind is important to enable
			# dynamic binding of table or column names:
			if ($instant_bind[$i] == '!') {
				$expr = str_replace(
					$matches[0][$i],
					$value,
					$expr
				);
				unset($matches[0][$i]);
			} else {
				$bindList[] = ':' . $paramID;

				# Save parameter
				$vars[$varPrefix . $paramID] = $value;

				# Obligatory param?
				if ($obligatory[$i] == '*') {
					$obligatoryParams[] = $paramID;
				}
			}
		}

		# Add array matches
		if (isset($addToMatches)) {
			$matches[0] = array_merge($matches[0], $addToMatches);
		}

		if (isset($addToBindList)) {
			$bindList = array_merge($bindList, $addToBindList);
		}

		# Make obligatory params unique
		$obligatoryParams = (isset($obligatoryParams[0]))
			? array_unique($obligatoryParams)
			: null;

		# Fix Expression
		$fixedExpr = str_replace(
			$matches[0],
			$bindList,
			$expr);
	}

	return array($vars, $fixedExpr, $obligatoryParams);
}

