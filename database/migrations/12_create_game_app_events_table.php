<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up()
	{
		Schema::create('game_app_events', function (Blueprint $table) {
			$table->id();
            $table->foreignId('game_app_id')->constrained();
			$table->string('name');
			$table->unique(['game_app_id', 'name']);
		});
	}

	public function down()
	{
		Schema::dropIfExists('game_app_events');
	}
};
