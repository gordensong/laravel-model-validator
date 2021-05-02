<?php

namespace GordenSong;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateModelValidatorCommand extends \Illuminate\Console\Command
{
	/**
	 * @var Filesystem $files
	 */
	protected $files;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'gs-make:model-validator {model*}';

	/**
	 * @var string
	 */
	protected $dir = 'app';

	/** @var Factory */
	protected $view;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate validator for models';

	/**
	 * @var string
	 */
	protected $existingFactories = '';

	/**
	 * @var array
	 */
	protected $properties = [];

	/**
	 * @var
	 */
	protected $force;

	/**
	 * @param Filesystem $files
	 * @param Factory $view
	 */
	public function __construct(Filesystem $files, Factory $view)
	{
		parent::__construct();
		$this->files = $files;
		$this->view = $view;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->dir = $this->option('dir');
		$this->force = $this->option('force');
		$models = $this->argument('model');

		$models = $this->loadModels($models);

		foreach ($models as $model) {
			$filename = 'app/Validatores/' . class_basename($model) . 'Validator.php';
			if ($this->files->exists($filename) && !$this->force) {
				$this->line('<fg=yellow>Model validator exists, use --force to overwrite:</fg=yellow> ' . $filename);
				continue;
			}

			$result = $this->generateModelValidator($model);
			if ($result === false) {
				continue;
			}

			$written = $this->files->put($filename, $result);
			if ($written !== false) {
				$this->line('<info>Model validator created:</info> ' . $filename);
			} else {
				$this->line('<error>Failed to create model validator:</error> ' . $filename);
			}
		}
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments(): array
	{
		return [
			['model', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Which models to include', []],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions(): array
	{
		return [
			['dir', 'D', InputOption::VALUE_OPTIONAL, 'The model directory', $this->dir],
			['force', 'F', InputOption::VALUE_NONE, 'Overwrite any existing model factory'],
		];
	}

	protected function generateModelValidator($model)
	{
		if (!class_exists($model)) {
			if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
				$this->error("Unable to find '$model' class");
			} else{
				$this->error("Unable to find '$model' class");
			}
			return false;
		}

		try {
			// handle abstract classes, interfaces, ...
			$reflectionClass = new ReflectionClass($model);

			if (!$reflectionClass->isSubclassOf('Illuminate\Database\Eloquent\Model')) {
				return false;
			}

			if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
				$this->comment("Loading model '$model'");
			}

			if (!$reflectionClass->IsInstantiable()) {
				// ignore abstract class or interface
				return false;
			}

			$model = $this->laravel->make($model);

			$table = TableUtil::loadFromModel($model);

			$meta = ModelValidatorMeta::make($table, $model);

			return $this->createModelValidator($meta);
		} catch (\Exception $e) {
			$this->error("Exception: " . $e->getMessage() . "\nCould not analyze class $model.");
			return false;
		}
	}

	/**
	 * @param ModelValidatorMeta $validatorMeta
	 * @return string
	 */
	public function createModelValidator(ModelValidatorMeta $validatorMeta): string
	{
		$output = '<?php' . "\n\n";
		$output .= $this->view
			->file(__DIR__ . '/model-validator.blade.php', $validatorMeta->toArray())
			->render();

		return $output;
	}

	/**
	 * @param array|string[] $models
	 * @return array|array[]|string[]|\string[][]
	 */
	protected function loadModels($models = []): array
	{
		if (!empty($models)) {
			return array_map(function ($name) {
				if (strpos($name, '\\') !== false) {
					return $name;
				}

				return str_replace(
					[DIRECTORY_SEPARATOR, basename($this->laravel->path()) . '\\'],
					['\\', $this->laravel->getNamespace()],
					$this->dir . DIRECTORY_SEPARATOR . $name
				);
			}, $models);
		}

		$dir = base_path($this->dir);
		if (!file_exists($dir)) {
			return [];
		}

		return array_map(function (\SplFIleInfo $file) {
			return str_replace(
				[DIRECTORY_SEPARATOR, basename($this->laravel->path()) . '\\'],
				['\\', $this->laravel->getNamespace()],
				$file->getPath() . DIRECTORY_SEPARATOR . basename($file->getFilename(), '.php')
			);
		}, $this->files->allFiles($this->dir));
	}
}
