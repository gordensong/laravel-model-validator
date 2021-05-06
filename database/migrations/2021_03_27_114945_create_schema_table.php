<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchemaTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('schema', function (Blueprint $table) {
			$table->id();
			$table->string('title', 50)->nullable();
			$table->integer('price')->nullable()->default(0);

			// $table->string('bar_code', 32)->nullable();
			// $table->string('author', 50)->nullable();
			// $table->tinyInteger('tiny_integer');
			// $table->smallInteger('small_integer');
			// $table->integer('integer');
			// $table->unsignedInteger('unsigned_integer');
			// $table->bigInteger('big_integer');
			// $table->json('json')->nullable();
			// $table->date('date');
			// $table->time('time');
			// $table->year('year');
			// $table->dateTime('datetime');
			// $table->timestamp('timestamp');
			// $table->float('float')->default(0);
			// $table->double('double')->default(0);
			// $table->decimal('decimal')->default(0)->nullable(true);
			// $table->text('text');
			// $table->mediumText('medium_text')->nullable(true);
			// $table->longText('long_text')->nullable(true);
			// $table->dateTime('published_at')->nullable();
			// $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('schema');
	}
}
