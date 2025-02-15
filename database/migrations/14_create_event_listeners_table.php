<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up()
	{
		Schema::create('event_listeners', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('game_event_listener_manager_id');
			$table->unsignedBigInteger('listenerable_id');
			$table->string('listenerable_type');
			$table->foreign('game_event_listener_manager_id')->references('id')->on('game_event_listener_managers')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::dropIfExists('event_listeners');
	}
};
