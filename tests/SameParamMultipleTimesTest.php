<?php

class SameParamMultipleTimesTest extends PHPUnit_Framework_TestCase
{
	public function testGet()
	{
		passthru('php index.php ' .
			'--application=tests ' .
			'--report=SameParamMultipleTimes ' .
			'--get1=1 ' .
			'--get2=1 ' .
			'--get3=1 ' .
			''
		);

		$this->expectOutputRegex('/get1 success/');
		$this->expectOutputRegex('/get2 success/');
		$this->expectOutputRegex('/get3 success/');

		$this->expectOutputRegex('/s1 success/');
		#$this->expectOutputRegex('/s2 success/');
		#$this->expectOutputRegex('/s3 success/');
	}
}
