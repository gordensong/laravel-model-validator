<?php


namespace Tests\Console\Command;


use GordenSong\Providers\LaravelModelValidatorServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Tests\TestCase;

class GenerateModelValidatorCommandTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$serviceProvider = new LaravelModelValidatorServiceProvider($this->app);
		$serviceProvider->register();
	}

	public function test_handle()
	{
		Artisan::call('php artisan make:gs-model-validator Card');
	}
}