<?php

namespace GordenSong;

use Illuminate\Support\ServiceProvider;

class ModelValidatorHelperServiceProvider extends ServiceProvider
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

		$this->commands('command.model-validator-helper.generate');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('command.model-validator-helper.generate');
	}

}
