<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration to create the 'games' table.
 * This table stores information about playtroughs of games.
 *
 * @package App\Migrations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('state', ['waiting', 'running', 'finished'])->default('waiting');
            $table->string('invitation_code')->nullable();
            $table->bigInteger('elapsed')->default(0);
            $table->boolean('auto_authorize_players')->default(false);
            $table->foreignId('game_app_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
