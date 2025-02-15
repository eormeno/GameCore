<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('game_objects', function (Blueprint $table) {
			$table->id();
			// The version of the object
			$table->bigInteger('version')->default(0);
			// The name of the object
			$table->string('name')->index();
			// Whether the object is active or not
			$table->boolean('active')->default(true);
			// Whether the object's parents are active or not
			$table->boolean('active_parents')->default(true);
			// The game that the object belongs to
			$table->foreignId('game_id')->constrained('games')->onDelete('cascade');
			// The parent object
			$table->foreignId('game_object_id')->nullable()->constrained('game_objects')->onDelete('cascade');
			// The current state of the object
			$table->string('state')->nullable();
			// Each state component indexed like: { 'gtn.initial-state-view': 1, 'gtn.preparing-state': 2, ... }
			$table->json('state_components')->nullable();
			// Each indexed child object like: { 'initial_view_2': 1, 'showing_view': 2, ... }
			$table->json('indexed_children')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('game_objects');
	}
};
