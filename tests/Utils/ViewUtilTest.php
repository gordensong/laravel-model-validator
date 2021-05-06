<?php


namespace Tests\Utils;


use GordenSong\Utils\ViewUtil;
use Tests\TestCase;

class ViewUtilTest extends TestCase
{
	public function test_blade()
	{
		self::assertTrue(file_exists(ViewUtil::getModelValidatorBladePath()));
	}
}