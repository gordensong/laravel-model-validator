<?php


namespace Tests;

use Faker\Factory;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerInterface;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
	protected function getEnvironmentSetUp($app)
	{
		parent::getEnvironmentSetUp($app);

//		$app['config']->set('database.default', 'mysql');
//		$app['config']->set('database.connections.mysql', [
//			'driver' => 'mysql',
//			'host' => env('DB_HOST', '127.0.0.1'),
//			'database' => 'laravel',
//			'username' => 'root',
//			'password' => env('DB_PASSWORD', ''),
//			'charset' => 'utf8',
//			'collation' => 'utf8_unicode_ci',
//			'prefix' => 'ims_',
//		]);
	}

}
