<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CardTest extends TestCase
{
	public function test_db()
	{
	    dd(DB::table('card')->get());
	}
}