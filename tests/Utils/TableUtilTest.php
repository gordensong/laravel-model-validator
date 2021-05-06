<?php

namespace Tests\Utils;

use Doctrine\DBAL\Schema\Table;
use GordenSong\Utils\TableUtil;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Models\Card;
use Tests\TestCase;

class TableUtilTest extends TestCase
{
	use RefreshDatabase;

	public function test_migration()
	{
		self::assertTrue(Schema::hasTable('card'));
	}

	public function test_load()
	{
		$table = TableUtil::load('card');

		self::assertInstanceOf(Table::class, $table);
		self::assertEquals('card', $table->getName());
	}

	public function test_loadFromModel()
	{
		$card = new Card();

		$table = TableUtil::loadFromModel($card);

		self::assertInstanceOf(Table::class, $table);
		self::assertEquals('card', $table->getName());
	}
}