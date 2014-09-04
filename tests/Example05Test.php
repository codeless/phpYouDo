<?php

/**
 * Checks if subreport are running properly.
 */

class Example05Test extends PHPUnit_Framework_TestCase
{
	public function testSubreport()
	{
		passthru('php index.php ' .
			'--application=example_05_subreport ' .
			'--report=report');

		$this->expectOutputRegex('/SUM(lorem)/');
	}
}
