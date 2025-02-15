<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_object_id')->constrained()->onDelete('cascade');
            $table->string('state')->nullable();
            $table->string('type');
            $table->boolean('enabled')->default(true);
            $table->json('messages')->nullable();
			$table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
