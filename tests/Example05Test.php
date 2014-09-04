<?php

/**
 * Checks if subreport are running properly.
 */

class Example05Test extends PHPUnit_Framework_TestCase
{
	public function testSubreport()
	{
		passthru('php index.php ' .
			'--application=example_05_subreports ' .
			'--report=report');

		$this->expectOutputRegex('/var count/');
		$this->expectOutputRegex('/var sum/');
	}
}
