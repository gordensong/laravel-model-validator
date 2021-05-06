<?php


namespace GordenSong\Utils;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ModelValidatorMeta implements Arrayable
{
	/**
	 * @var array
	 */
	private $rules;
	/**
	 * @var Model|null
	 */
	private $model;
	/**
	 * @var Table
	 */
	private $table;

	public function __construct(Table $table, Model $model = null)
	{
		$this->table = $table;
		$this->model = $model;
	}

	public function toArray(): array
	{
		return [
			'name' => $this->getClassName(),
			'rules' => $this->getRules(),
		];
	}

	public static function make(Table $table, Model $model = null): ModelValidatorMeta
	{
		return new self($table, $model);
	}

	public function getClassName(): string
	{
		return $this->model
			? class_basename($this->model)
			: Str::studly($this->table->getName());
	}

	public function getRules(): array
	{
		if (empty($this->rules)) {
			foreach ($this->table->getColumns() as $column) {
				$this->rules[$column->getName()] = $this->columnRules($column, $this->model);
			}
		}
		return $this->rules;
	}

	private function columnRules(Column $column, Model $model = null): array
	{
		$rules = [$validationType = $this->validationType($column, $model),];

		$length = $column->getLength();

		if ($validationType === 'string' && $length) {
			$rules[] = "max:{$length}";
		}
		if ($validationType === 'numeric' && $column->getUnsigned()) {
			if ($model && $model->getKeyName() === $column->getName() && $model->incrementing) {
				$rules[] = 'min:1';
			} else {
				$rules[] = 'min:0';
			}
		}
		if (!$column->getNotnull()) {
			$rules[] = 'nullable';
		}

		return $rules;
	}

	private function validationType(Column $column, Model $model = null)
	{
		$name = $column->getName();

		if ($model && in_array($name, $model->getDates())) {
			$type = 'datetime';
		} else {
			$type = $column->getType()->getName();
		}

		$validationTypes = [
			'date' => 'date',
			'time' => 'time',
			'datetime' => 'datetime',

			'decimal' => 'numeric',
			'float' => 'numeric',

			'boolean' => 'boolean',
			'smallint' => 'numeric',
			'integer' => 'numeric',
			'bigint' => 'numeric',

			'string' => 'string',
			'text' => 'string',

			'json' => 'array',
		];

		return Arr::get($validationTypes, $type, 'string');
	}
}
