<?php

namespace GordenSong\Console\Command;

use GordenSong\Utils\ModelValidatorMeta;
use GordenSong\Utils\TableUtil;
use GordenSong\Utils\ViewUtil;
use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class GenerateTableValidatorCommand extends \Illuminate\Console\Command
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
	protected $name = 'make:gs-table-validator {table} {--connection=mysql}';

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
	protected $description = 'Generate validator for tables';

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

		$tables = $this->argument('table');

		foreach ($tables as $table) {
			$filename = 'app/Validators/' . Str::studly($table) . 'Validator.php';
			$filename = base_path($filename);

			if ($this->files->exists($filename) && !$this->force) {
				$this->line('<fg=yellow>Model validator exists, use --force to overwrite:</fg=yellow> ' . $filename);
				continue;
			}

			$result = $this->generateModelValidator($table);
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
			['table', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Which models to include', []],
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

	protected function generateModelValidator($tableName)
	{
		try {
			$table = TableUtil::load($tableName);

			$meta = ModelValidatorMeta::make($table);

			return $this->createModelValidator($meta);
		} catch (\Exception $e) {
			$this->error("Exception: " . $e->getMessage() . "\nCould not analyze class $tableName.");
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
			->file(ViewUtil::getModelValidatorBladePath(), $validatorMeta->toArray())
			->render();

		return $output;
	}
}
