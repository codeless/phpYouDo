<?php

/**
 * Tests if session variables are incorporated into SQL statements,
 * even when the CLI is used.
 */

class CLISessionTest extends PHPUnit_Framework_TestCase
{
	public function testSessionVariablesThroughCLI()
	{
		passthru('php index.php ' .
			'--application=tests ' .
			'--report=CLISession');

		$this->expectOutputRegex('/var get/');
	}
}
