<?php

namespace GordenSong\Providers;

use GordenSong\Console\Command\GenerateModelValidatorCommand;
use GordenSong\Console\Command\GenerateTableValidatorCommand;
use GordenSong\ModelValidator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

class LaravelModelValidatorServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('command.model-validator-helper.generate', function ($app) {
			return new GenerateModelValidatorCommand($app['files'], $app['view']);
		});

		$this->app->bind('command.table-validator-helper.generate', function ($app) {
			return new GenerateTableValidatorCommand($app['files'], $app['view']);
		});

		$this->commands('command.model-validator-helper.generate');
		$this->commands('command.table-validator-helper.generate');

		ModelValidator::setValidatorFactory(app()->make(Factory::class));
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides(): array
	{
		return [
			'command.model-validator-helper.generate',
			'command.table-validator-helper.generate'
		];
	}

}
