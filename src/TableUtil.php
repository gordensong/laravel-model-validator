<?php


namespace GordenSong;


use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TableUtil
{
	private static $tables = [];

	public static function load(string $table, string $connectionName = null): Table
	{
		if ($tableSchema = data_get(self::$tables, "{$connectionName}.{$table}")) {
			return $tableSchema;
		}

		$connection = Schema::connection($connectionName)->getConnection();
		$table = $connection->getTablePrefix() . $table;

		$database = null;
		if (strpos($table, '.')) {
			[$database, $table] = explode('.', $table);
		}

		$tableSchema = $connection->getDoctrineSchemaManager()->listTableDetails($table);

		data_set(self::$tables, "{$connectionName}.{$table}", $tableSchema);

		return $tableSchema;
	}

	public static function loadFromModel(Model $model): Table
	{
		$tableName = $model->getTable();
		$connectionName = $model->getConnectionName();

		return self::load($tableName, $connectionName);
	}
}
