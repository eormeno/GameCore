<?php

namespace App\Services\Screens;

use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

class ImageUploadDemoService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->shadow(false)
            ->padding(20);

        // Title
        $container->add(
            UIBuilder::label('title')
                ->text('📷 Demo de Image Upload')
                ->style('h1')
                ->center()
        );

        $container->add(
            UIBuilder::label('description')
                ->text('Prueba el componente de upload de imágenes con diferentes configuraciones')
                ->style('h4')
                ->center()
        );

        // Examples Container
        $examplesContainer = UIBuilder::container('examples')
            ->layout(LayoutType::VERTICAL)
            ->padding(20)
            ->border(true)
            ->justifyContent('center')
            ->alignItems('center')
            ->shadow(false);

        $examplesContainer->add(
            UIBuilder::imageUpload('basic_upload')
                ->callback('handle_basic_upload')
                ->storage('uploads/basic')
                ->validation('image/*', 5242880, 1) // 5MB, 1 file
                ->labels('Subir imagen básica', 'Arrastra tu imagen aquí o haz clic para seleccionar')
        );

        // Multiple Upload
        // $examplesContainer->add(
        //     UIBuilder::label('multiple_title')
        //         ->text('📁 Upload Múltiple')
        //         ->style('h3')
        // );

        // $examplesContainer->add(
        //     UIBuilder::imageUpload('multiple_upload')
        //         ->callback('handle_multiple_upload')
        //         ->storage('uploads/gallery', 'gallery')
        //         ->multiple(5) // Up to 5 files
        //         ->validation('image/jpeg,image/png,image/webp', 10485760, 5) // 10MB, 5 files
        //         ->theme('success')
        //         ->labels('Galería de imágenes', 'Puedes subir hasta 5 imágenes')
        // );

        // Compact Upload
        // $examplesContainer->add(
        //     UIBuilder::label('compact_title')
        //         ->text('📦 Upload Compacto')
        //         ->style('h3')
        // );

        // $examplesContainer->add(
        //     UIBuilder::imageUpload('compact_upload')
        //         ->callback('handle_compact_upload')
        //         ->storage('uploads/avatars', 'avatar')
        //         ->compact()
        //         ->validation('image/*', 2097152, 1) // 2MB, 1 file
        //         ->theme('primary')
        //         ->labels('Avatar de usuario', null, '📷 Seleccionar Avatar')
        // );

        // Custom Upload with Compression
        // $examplesContainer->add(
        //     UIBuilder::label('compressed_title')
        //         ->text('🗜️ Upload con Compresión')
        //         ->style('h3')
        // );

        // $examplesContainer->add(
        //     UIBuilder::imageUpload('compressed_upload')
        //         ->callback('handle_compressed_upload')
        //         ->storage('uploads/compressed', 'comp')
        //         ->compress(0.7, 1200, 800) // 70% quality, max 1200x800
        //         ->dimensions(100, 100, 2000, 2000) // Min 100x100, max 2000x2000
        //         ->theme('warning')
        //         ->size('large')
        //         ->labels('Upload con compresión automática')
        // );

        $container->add($examplesContainer);

        // Results Container
        $resultsContainer = UIBuilder::container('results')
            ->layout(LayoutType::VERTICAL)
            ->padding(20)
            ->shadow(true);

        $resultsContainer->add(
            UIBuilder::label('results_title')
                ->text('📋 Resultados de Upload')
                ->style('h3')
        );

        $resultsContainer->add(
            UIBuilder::label('results_content')
                ->text('Los resultados de los uploads aparecerán aquí...')
                ->style('default')
        );

        $container->add($resultsContainer);

        return $container;
    }

    /**
     * Handle basic upload callback
     */
    public function onHandleBasicUpload(array $params): array
    {
        $filesCount = $params['files_count'] ?? 0;
        $files = $params['files'] ?? [];
        
        if ($filesCount === 0) {
            return $this->getUpdatedResults('❌ Error: No se subieron archivos');
        }

        if ($filesCount === 1 && !empty($files)) {
            $file = $files[0];
            
            $message = "✅ Upload básico completado:\n";
            $message .= "📁 Archivo: " . ($file['name'] ?? 'nombre no disponible') . "\n";
            $message .= "📏 Tamaño: " . $this->formatBytes($file['size'] ?? 0) . "\n";
            $message .= "🔗 URL: " . ($file['url'] ?? 'URL no disponible');
        } else {
            $message = "✅ Upload básico completado: {$filesCount} archivo(s) subido(s)";
        }

        return $this->getUpdatedResults($message);
    }

    /**
     * Handle multiple upload callback
     */
    // public function onHandleMultipleUpload(array $params): array
    // {
    //     $filesCount = $params['files_count'] ?? 0;
    //     $files = $params['files'] ?? [];
        
    //     if ($filesCount === 0) {
    //         return $this->getUpdatedResults('❌ Error en upload múltiple');
    //     }

    //     $message = "✅ Galería actualizada:\n";
    //     $message .= "📊 {$filesCount} archivo(s) subido(s)\n";
        
    //     if (!empty($files)) {
    //         $totalSize = array_sum(array_column($files, 'size'));
    //         $message .= "📏 Tamaño total: " . $this->formatBytes($totalSize) . "\n";
    //         $message .= "📁 Archivos:\n";
    //         foreach ($files as $file) {
    //             $message .= "  • {$file['name']} (" . $this->formatBytes($file['size']) . ")\n";
    //         }
    //     }

    //     return $this->getUpdatedResults($message);
    // }

    // /**
    //  * Handle compact upload callback
    //  */
    // public function onHandleCompactUpload(array $params): array
    // {
    //     $filesCount = $params['files_count'] ?? 0;
    //     $files = $params['files'] ?? [];
        
    //     if ($filesCount === 0) {
    //         return $this->getUpdatedResults('❌ Error en upload compacto');
    //     }

    //     if (!empty($files)) {
    //         $file = $files[0];
    //         $message = "✅ Avatar actualizado correctamente:\n";
    //         $message .= "📁 {$file['name']}\n";
    //         $message .= "📏 " . $this->formatBytes($file['size']) . "\n";
    //         $message .= "🔗 {$file['url']}";
    //     } else {
    //         $message = "✅ Avatar actualizado correctamente";
    //     }

    //     return $this->getUpdatedResults($message);
    // }

    // /**
    //  * Handle compressed upload callback
    //  */
    // public function onHandleCompressedUpload(array $params): array
    // {
    //     $filesCount = $params['files_count'] ?? 0;
    //     $files = $params['files'] ?? [];
        
    //     if ($filesCount === 0) {
    //         return $this->getUpdatedResults('❌ Error en upload comprimido');
    //     }

    //     if (!empty($files)) {
    //         $file = $files[0];
    //         $message = "✅ Imagen comprimida subida:\n";
    //         $message .= "📁 {$file['name']}\n";
    //         $message .= "📏 " . $this->formatBytes($file['size']) . "\n";
    //         $message .= "🗜️ Optimizada automáticamente\n";
    //         $message .= "🔗 {$file['url']}";
    //     } else {
    //         $message = "✅ Imagen comprimida subida correctamente";
    //     }

    //     return $this->getUpdatedResults($message);
    // }

    /**
     * Update results display
     */
    private function getUpdatedResults(string $message): array
    {
        return [
            'action' => 'ui_update',
            'updates' => [
                'results_content' => [
                    'text' => $message
                ]
            ]
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}