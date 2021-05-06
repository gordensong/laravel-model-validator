<?php

namespace GordenSong;

use GordenSong\Exceptions\ValidatorFactoryNotFoundException;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Class ModelValidator
 * @package GordenSong
 */
class ModelValidator
{
	/** @var array */
	protected $data;
	/** @var array */
	protected $rules = [];
	/** @var array */
	protected $messages = [];
	/** @var array */
	protected $customerAttributes = [];

	/** @var array */
	protected $validated = [];
	/** @var bool|null */
	protected $passes = null;
	/** @var Validator */
	protected $validator;
	/** @var Factory */
	protected static $validatorFactory;

	public function __construct(array $data = [], array $rules = [])
	{
		$this->data = $data ?: [];
		$this->rules = $rules ?: $this->rules;
	}

	public static function make(array $data = [], array $rules = []): ModelValidator
	{
		return new static($data, $rules);
	}

	/**
	 * @return Factory
	 * @throws ValidatorFactoryNotFoundException
	 */
	private static function getValidatorFactory(): Factory
	{
		if (empty(static::$validatorFactory)) {
			throw new ValidatorFactoryNotFoundException();
		}
		return static::$validatorFactory;
	}

	/**
	 * @param \Illuminate\Contracts\Validation\Factory $validatorFactory
	 */
	public static function setValidatorFactory(\Illuminate\Contracts\Validation\Factory $validatorFactory)
	{
		static::$validatorFactory = $validatorFactory;
	}

	/**
	 * @return array
	 * @throws InvalidArgumentException
	 */
	private function getData(): array
	{
		if (empty($this->data)) {
			throw new InvalidArgumentException('参数错误:要验证的数据不存在');
		}
		return $this->data;
	}

	/**
	 * @throws ValidationException
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function validate(): array
	{
		if ($this->fails()) {
			throw new ValidationException($this->validator);
		}
		return $this->validated = ($this->validated ?: $this->validator->validated());
	}

	/**
	 * @throws ValidationException
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function validated(): array
	{
		if ($this->validated) {
			return $this->validated;
		}
		return $this->validate();
	}

	/**
	 * @return bool
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function fails(): bool
	{
		return !$this->passes();
	}

	/**
	 * @return bool
	 * @throws InvalidArgumentException
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function passes(): bool
	{
		if (is_bool($this->passes)) {
			return $this->passes;
		}

		$this->validator = self::getValidatorFactory()
			->make(
				$this->getData(),
				$this->getRules(),
				$this->getMessages(),
				$this->getCustomAttributes()
			);

		return $this->passes = $this->validator->passes();
	}

	public function getRules(): array
	{
		return $this->rules ?: [];
	}

	private function getMessages(): array
	{
		return $this->messages ?: [];
	}

	private function getCustomAttributes(): array
	{
		return $this->customerAttributes ?: [];
	}

	/**
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function getErrors(): array
	{
		$this->makeSureValidated();

		return $this->getMessageBag()->all();
	}

	/**
	 * @return MessageBag
	 * @throws ValidatorFactoryNotFoundException
	 */
	public function getMessageBag(): MessageBag
	{
		$this->makeSureValidated();

		return $this->validator->getMessageBag();
	}

	/**
	 * @throws ValidatorFactoryNotFoundException
	 */
	private function makeSureValidated(): void
	{
		$this->passes();
	}

	public function setRules(array $rules)
	{
		$this->rules = $this->splitRules($rules);
	}

	/**
	 * @param $key
	 * @return array|string
	 */
	public function getRule($key)
	{
		return data_get($this->getRules(), $key);
	}

	/**
	 * @param $field
	 * @return bool
	 */
	public function hasField($field): bool
	{
		return array_key_exists($field, $this->rules);
	}

	/**
	 * 只修改已有
	 * @param string|array|null $fields
	 * @return $this
	 */
	public function required(...$fields): ModelValidator
	{
		if (func_num_args() == 0) {
			$fields = array_keys($this->rules);
		} else {
			$field = func_get_arg(0);
			$fields = is_array($field) ? $field : $fields;
		}
		foreach ($fields as $field) {
			$field = (string)$field;
			$rule = $this->getRule($field);
			if (is_string($rule)) {
				if ($rule && !Str::contains($rule, 'required')) {
					$this->rules[$field] .= '|required';
				}
			} elseif (is_array($rule)) {
				if (!in_array('required', $rule)) {
					$this->rules[$field][] = 'required';
				}
			}
		}
		return $this;
	}

	/**
	 * 排除字段
	 * @param string|array $fields
	 * @return $this
	 */
	public function exclude(...$fields): ModelValidator
	{
		if (func_num_args() == 0) {
			return $this;
		}
		$field = func_get_arg(0);
		$fields = is_array($field) ? $field : $fields;
		foreach ($fields as $field) {
			$field = (string)$field;
			unset($this->rules[$field]);
		}
		return $this;
	}

	public function only(...$fields): ModelValidator
	{
		if (func_num_args() == 0) {
			return $this;
		}

		$field = func_get_arg(0);
		$fields = is_array($field) ? $field : $fields;

		$rules = [];
		foreach ($fields as $field) {
			$rule = $this->getRule($field);
			if ($rule) {
				$rules[$field] = $rule;
			}
		}
		$this->rules = $rules;

		return $this;
	}

	private function splitRules(array $rules): array
	{
		foreach ($rules as $field => $rule) {
			$rules[$field] = $this->splitRule($rule);
		}
		return $rules;
	}

	private function splitRule($rule)
	{
		if (is_string($rule)) {
			return explode('|', $rule);
		} elseif (is_array($rule)) {
			return $rule;
		}
		return [];
	}
}
