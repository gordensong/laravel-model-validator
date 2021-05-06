<?php


namespace Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
	}
}
