<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('role')->default('player'); // enum GameUserRole (puede ser 'owner', 'administrator', 'tester', 'player')
            $table->string('status')->default('pending_owner_approval'); // enum GameUserStatus (puede ser 'pending_owner_approval', 'pending_player_acceptance', 'active', 'left', 'kicked', 'banned')
            $table->string('join_method')->nullable(); // enum GameUserJoinMethod, puede ser 'invite', 'join_code', etc.
            $table->foreignId('actioned_by')->nullable()->constrained('users'); // Quien hizo la última acción
            $table->text('reason')->nullable(); // Razón de kick/ban
            $table->timestamp('joined_at')->nullable(); // Cuando se volvió activo
            $table->timestamp('left_at')->nullable(); // Cuando abandonó/fue expulsado
            $table->timestamps();

            // Índice único para evitar duplicados en la relación game-user
            $table->unique(['game_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_user');
    }
};
