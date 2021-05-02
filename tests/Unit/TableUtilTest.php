<?php

namespace Tests\Unit;

use Doctrine\DBAL\Schema\Table;
use GordenSong\TableUtil;
use Tests\Models\Card;
use Tests\TestCase;

class TableUtilTest extends TestCase
{
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