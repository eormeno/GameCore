<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TranslationInitCommand extends Command
{
    protected $signature = 'translation:init {--file=translations.csv} {--force}';
    protected $description = 'Initialize CSV file with base translations';

    public function handle()
    {
        $filename = $this->option('file');
        $filePath = resource_path("lang/$filename");
        
        // Check if file already exists
        if (file_exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("File $filename already exists. Overwrite?")) {
                $this->info("Operation cancelled.");
                return 0;
            }
        }
        
        // Create directory if it doesn't exist
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        $this->info("Creating base translations CSV file...");
        
        // Create CSV with base translations
        $file = fopen($filePath, 'w');
        
        if (!$file) {
            $this->error("❌ Could not create file: $filePath");
            return 1;
        }
        
        // Write headers
        fputcsv($file, ['Module', 'Key', 'Slug', 'EN', 'ES']);
        
        // Base translations
        $baseTranslations = $this->getBaseTranslations();
        
        foreach ($baseTranslations as $translation) {
            fputcsv($file, $translation);
        }
        
        fclose($file);
        
        $this->info("✅ CSV file created successfully: $filePath");
        $this->info("📊 Added " . count($baseTranslations) . " base translations");
        
        return 0;
    }
    
    private function getBaseTranslations(): array
    {
        return [
            // Common translations
            ['common', 'welcome', 'common.welcome', 'Welcome', 'Bienvenido'],
            ['common', 'welcome_message', 'common.welcome_message', 'Welcome to our amazing game!', '¡Bienvenido a nuestro increíble juego!'],
            ['common', 'loading', 'common.loading', 'Loading...', 'Cargando...'],
            ['common', 'save', 'common.save', 'Save', 'Guardar'],
            ['common', 'cancel', 'common.cancel', 'Cancel', 'Cancelar'],
            ['common', 'delete', 'common.delete', 'Delete', 'Eliminar'],
            ['common', 'edit', 'common.edit', 'Edit', 'Editar'],
            ['common', 'create', 'common.create', 'Create', 'Crear'],
            ['common', 'update', 'common.update', 'Update', 'Actualizar'],
            ['common', 'back', 'common.back', 'Back', 'Atrás'],
            ['common', 'next', 'common.next', 'Next', 'Siguiente'],
            ['common', 'previous', 'common.previous', 'Previous', 'Anterior'],
            ['common', 'yes', 'common.yes', 'Yes', 'Sí'],
            ['common', 'no', 'common.no', 'No', 'No'],
            ['common', 'ok', 'common.ok', 'OK', 'OK'],
            ['common', 'close', 'common.close', 'Close', 'Cerrar'],
            ['common', 'success', 'common.success', 'Success', 'Éxito'],
            ['common', 'error', 'common.error', 'Error', 'Error'],
            ['common', 'warning', 'common.warning', 'Warning', 'Advertencia'],
            ['common', 'info', 'common.info', 'Information', 'Información'],
            
            // Error translations
            ['errors', 'game_not_found', 'errors.game_not_found', 'Game not found or is not active.', 'Juego no encontrado o no está activo.'],
            ['errors', 'error_occurred', 'errors.error_occurred', 'An error occurred while trying to play the game.', 'Ocurrió un error al intentar jugar el juego.'],
            ['errors', 'new_game_error', 'errors.new_game_error', 'An error occurred while trying to create a new game.', 'Ocurrió un error al intentar crear un nuevo juego.'],
            ['errors', 'resource_not_found', 'errors.resource_not_found', 'Resource :resource not found', 'Recurso :resource no encontrado'],
            ['errors', 'server_error', 'errors.server_error', 'An internal server error occurred.', 'Ocurrió un error interno del servidor.'],
            ['errors', 'validation_failed', 'errors.validation_failed', 'Validation failed.', 'Falló la validación.'],
            ['errors', 'unauthorized', 'errors.unauthorized', 'Unauthorized access.', 'Acceso no autorizado.'],
            ['errors', 'forbidden', 'errors.forbidden', 'Access forbidden.', 'Acceso prohibido.'],
            
            // Game translations
            ['games', 'new_game_created', 'games.new_game_created', 'New game created successfully', 'Nuevo juego creado exitosamente'],
            ['games', 'game_started', 'games.game_started', 'Game started', 'Juego iniciado'],
            ['games', 'game_finished', 'games.game_finished', 'Game finished', 'Juego terminado'],
            ['games', 'player_joined', 'games.player_joined', 'Player joined the game', 'Jugador se unió al juego'],
            ['games', 'player_left', 'games.player_left', 'Player left the game', 'Jugador abandonó el juego'],
            ['games', 'level_completed', 'games.level_completed', 'Level completed', 'Nivel completado'],
            ['games', 'game_over', 'games.game_over', 'Game Over', 'Fin del Juego'],
            ['games', 'you_win', 'games.you_win', 'You Win!', '¡Has Ganado!'],
            ['games', 'score', 'games.score', 'Score', 'Puntuación'],
            ['games', 'level', 'games.level', 'Level', 'Nivel'],
            ['games', 'lives', 'games.lives', 'Lives', 'Vidas'],
            ['games', 'time', 'games.time', 'Time', 'Tiempo'],
            ['games', 'pause', 'games.pause', 'Pause', 'Pausar'],
            ['games', 'resume', 'games.resume', 'Resume', 'Continuar'],
            ['games', 'restart', 'games.restart', 'Restart', 'Reiniciar'],
            ['games', 'quit', 'games.quit', 'Quit', 'Salir'],
            ['games', 'settings', 'games.settings', 'Settings', 'Configuración'],
            ['games', 'sound', 'games.sound', 'Sound', 'Sonido'],
            ['games', 'music', 'games.music', 'Music', 'Música'],
            ['games', 'fullscreen', 'games.fullscreen', 'Fullscreen', 'Pantalla completa'],
        ];
    }
}