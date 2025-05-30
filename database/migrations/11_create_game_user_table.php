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
            $table->boolean('is_owner')->default(false);
            $table->boolean('is_administrator')->default(false);
            $table->boolean('is_tester')->default(false);
            $table->enum('status', [
                'pending_owner_approval',    // Usuario solicitó acceso
                'pending_player_acceptance', // Owner invitó al usuario
                'active',                    // Jugador activo en partida
                'left',                      // Usuario abandonó
                'kicked',                    // Usuario fue expulsado
                'banned'                     // Usuario fue baneado
            ])->default('pending_owner_approval');
            $table->enum('join_method', ['request', 'invitation', 'auto'])->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('users'); // Quien hizo la última acción
            $table->text('reason')->nullable(); // Razón de kick/ban
            $table->timestamp('joined_at')->nullable(); // Cuando se volvió activo
            $table->timestamp('left_at')->nullable(); // Cuando abandonó/fue expulsado
            $table->timestamps();
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
