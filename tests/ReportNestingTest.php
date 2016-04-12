<?php

class ReportNestingTest extends PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		passthru('php index.php ' .
			'--application=tests ' .
			'--report=ReportNesting'
		);

		$this->expectOutputRegex('/Inside Subreport1/');
		$this->expectOutputRegex('/Inside Subreport2/');
		$this->expectOutputRegex('/Inside Subreport3/');
	}
}
