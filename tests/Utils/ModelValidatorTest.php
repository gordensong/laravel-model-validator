<?php


namespace Tests\Utils;


use GordenSong\ModelValidator;
use Illuminate\Validation\Factory;
use Tests\TestCase;

class ModelValidatorTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		ModelValidator::setValidatorFactory($this->app->make(Factory::class));
	}

	public function test_required_title()
	{
		$validator = ModelValidator::make([], [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		$validator->required('title');

		$rule = $validator->getRule('title');
		self::assertTrue(in_array('required', $rule));

		$rule = $validator->getRule('id');
		self::assertFalse(in_array('required', $rule));
	}

	public function test_required_all()
	{
		$validator = ModelValidator::make([], [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		$validator->required();

		$rule = $validator->getRule('title');
		self::assertTrue(in_array('required', $rule));

		$rule = $validator->getRule('id');
		self::assertTrue(in_array('required', $rule));
	}

	public function test_only()
	{
		$validator = ModelValidator::make([], [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		$validator->only();

		self::assertCount(2, $validator->getRules());

		$validator->only('title');

		self::assertCount(1, $validator->getRules());
		self::assertNotNull($validator->getRule('title'));
		self::assertNull($validator->getRule('id'));
		self::assertNull($validator->getRule('price'));
	}

	public function test_exclude()
	{
		$validator = ModelValidator::make([], [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
			'price' => ['numeric'],
		]);

		$validator->exclude();

		self::assertCount(3, $validator->getRules());

		$validator->exclude('id', 'title');

		self::assertCount(1, $validator->getRules());
		self::assertNotNull($validator->getRule('price'));
		self::assertNull($validator->getRule('id'));
		self::assertNull($validator->getRule('title'));
	}

	public function test_pass_empty_data()
	{
		$data = [];

		$validator = ModelValidator::make($data, [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		try {
			$validator->passes();
			self::fail('参数错误');
		} catch (\InvalidArgumentException $e) {
			self::assertTrue(true);
		}
	}

	public function passesProvider(): array
	{
		return [
			[$data = ['id' => 1], $expect = true],
			[$data = ['title' => 'title'], $expect = true],
			[$data = ['title' => 'title', 'id' => 1], $expect = true],
			[$data = ['title' => 'title', 'id' => 1, 'price' => 1], $expect = true],
		];
	}

	/**
	 * @dataProvider passesProvider
	 */
	public function test_pass(array $data = ['id' => 1], bool $expect = true)
	{
		$validator = ModelValidator::make($data, [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		self::assertEquals($expect, $validator->passes());
	}

	public function validateProvider(): array
	{
		return [
			[$data = ['id' => 1], $expect = ['id' => 1]],
			[$data = ['id' => 1, 'title' => 'title'], $expect = ['id' => 1, 'title' => 'title']],
			[$data = ['id' => 1, 'title' => 'title', 'price' => 1], $expect = ['id' => 1, 'title' => 'title']],
		];
	}

	/**
	 * @dataProvider validateProvider
	 */
	public function test_validate(array $data = ['id' => 1], array $expect = ['id' => 1])
	{
		$validator = ModelValidator::make($data, [
			'id' => ['numeric'],
			'title' => ['string', 'min:1', 'max:50'],
		]);

		self::assertEquals($expect, $validator->validate());
	}
}