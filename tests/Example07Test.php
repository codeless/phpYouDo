<?php

class Example07Test extends PHPUnit_Framework_TestCase
{
	public function testCreate()
	{
		passthru('php index.php ' .
			'--application=example_07_crud ' .
			'--report=create ' .
			'--submit="Save new" ' .
			'--ipsum=Testing ' .
			'--dolor=1 ' .
			'--sit=1 ' .
			'--amet=1 ');

		$this->expectOutputRegex('/Record inserted/');
	}
}
