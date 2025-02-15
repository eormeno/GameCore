<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up()
	{
		Schema::create('game_event_listener_managers', function (Blueprint $table) {
			$table->id();
            $table->foreignId('game_app_event_id')->constrained();
		});
	}

	public function down()
	{
		Schema::dropIfExists('game_event_listener_managers');
	}
};
