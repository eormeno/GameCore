<?php

namespace App\Services\UI\Components;

/**
 * Builder for Image Upload UI components
 * 
 * Modern drag-and-drop image upload component with preview, validation,
 * and customizable storage options. Supports multiple files, image
 * compression, and real-time preview.
 */
class ImageUploadBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            // Component type
            'type' => 'image_upload',
            
            // Core functionality
            'upload_url' => '/api/ui-upload', // Upload endpoint
            'callback_action' => null, // Backend callback method name
            'storage_path' => 'uploads', // Storage directory
            'prefix' => null, // File prefix (auto-generated if null)
            
            // File validation
            'accept' => 'image/*', // Accepted file types
            'max_file_size' => 5242880, // 5MB in bytes
            'max_files' => 1, // Maximum number of files
            'min_width' => null, // Minimum image width
            'min_height' => null, // Minimum image height
            'max_width' => null, // Maximum image width
            'max_height' => null, // Maximum image height
            
            // Visual style
            'style' => 'default', // default, compact, minimal, bordered
            'variant' => 'dropzone', // dropzone, button, inline
            'size' => 'medium', // small, medium, large
            'theme' => 'primary', // primary, secondary, success, etc.
            
            // UI options
            'show_preview' => true, // Show image preview
            'show_progress' => true, // Show upload progress
            'show_file_info' => true, // Show file name, size, etc.
            'allow_remove' => true, // Allow removing uploaded files
            'drag_drop' => true, // Enable drag and drop
            
            // Labels and messages
            'label' => null, // Component label
            'placeholder' => 'Arrastra imágenes aquí o haz clic para seleccionar',
            'drop_message' => 'Suelta las imágenes aquí',
            'upload_button_text' => 'Seleccionar imágenes',
            'error_message' => null, // Custom error message
            
            // Compression (optional)
            'compress' => false, // Enable client-side compression
            'quality' => 0.8, // Compression quality (0.1 - 1.0)
            'max_compressed_width' => 1920,
            'max_compressed_height' => 1080,
            
            // Advanced options
            'auto_upload' => true, // Upload immediately after selection
            'chunk_upload' => false, // Enable chunked upload for large files
            'chunk_size' => 1048576, // 1MB chunks
            'retry_attempts' => 3, // Retry failed uploads
            
            // Validation messages
            'messages' => [
                'file_too_large' => 'El archivo es demasiado grande',
                'invalid_type' => 'Tipo de archivo no válido',
                'too_many_files' => 'Demasiados archivos seleccionados',
                'upload_failed' => 'Error al subir el archivo',
                'dimensions_invalid' => 'Dimensiones de imagen no válidas'
            ]
        ];
    }

    /**
     * Set the upload callback action
     * 
     * @param string $action Method name in the calling service
     * @return self
     */
    public function callback(string $action): self
    {
        return $this->setConfig('callback_action', $action);
    }

    /**
     * Set storage configuration
     * 
     * @param string $path Storage directory path
     * @param string|null $prefix File prefix (null for auto-generation)
     * @return self
     */
    public function storage(string $path, ?string $prefix = null): self
    {
        return $this->setConfig('storage_path', $path)
                   ->setConfig('prefix', $prefix);
    }

    /**
     * Set file validation rules
     * 
     * @param string $accept Accepted file types (e.g., 'image/*', '.jpg,.png')
     * @param int $maxSize Maximum file size in bytes
     * @param int $maxFiles Maximum number of files
     * @return self
     */
    public function validation(string $accept = 'image/*', int $maxSize = 5242880, int $maxFiles = 1): self
    {
        return $this->setConfig('accept', $accept)
                   ->setConfig('max_file_size', $maxSize)
                   ->setConfig('max_files', $maxFiles);
    }

    /**
     * Set image dimension constraints
     * 
     * @param int|null $minWidth Minimum width
     * @param int|null $minHeight Minimum height
     * @param int|null $maxWidth Maximum width
     * @param int|null $maxHeight Maximum height
     * @return self
     */
    public function dimensions(?int $minWidth = null, ?int $minHeight = null, ?int $maxWidth = null, ?int $maxHeight = null): self
    {
        return $this->setConfig('min_width', $minWidth)
                   ->setConfig('min_height', $minHeight)
                   ->setConfig('max_width', $maxWidth)
                   ->setConfig('max_height', $maxHeight);
    }

    /**
     * Enable image compression
     * 
     * @param float $quality Compression quality (0.1 - 1.0)
     * @param int $maxWidth Maximum compressed width
     * @param int $maxHeight Maximum compressed height
     * @return self
     */
    public function compress(float $quality = 0.8, int $maxWidth = 1920, int $maxHeight = 1080): self
    {
        return $this->setConfig('compress', true)
                   ->setConfig('quality', $quality)
                   ->setConfig('max_compressed_width', $maxWidth)
                   ->setConfig('max_compressed_height', $maxHeight);
    }

    /**
     * Set component style
     * 
     * @param string $style Style variant
     * @return self
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set component variant
     * 
     * @param string $variant Variant type (dropzone, button, inline)
     * @return self
     */
    public function variant(string $variant): self
    {
        return $this->setConfig('variant', $variant);
    }

    /**
     * Set component size
     * 
     * @param string $size Size variant (small, medium, large)
     * @return self
     */
    public function size(string $size): self
    {
        return $this->setConfig('size', $size);
    }

    /**
     * Set component theme
     * 
     * @param string $theme Theme color
     * @return self
     */
    public function theme(string $theme): self
    {
        return $this->setConfig('theme', $theme);
    }

    /**
     * Set custom labels
     * 
     * @param string|null $label Component label
     * @param string|null $placeholder Placeholder text
     * @param string|null $buttonText Upload button text
     * @return self
     */
    public function labels(?string $label = null, ?string $placeholder = null, ?string $buttonText = null): self
    {
        if ($label !== null) $this->setConfig('label', $label);
        if ($placeholder !== null) $this->setConfig('placeholder', $placeholder);
        if ($buttonText !== null) $this->setConfig('upload_button_text', $buttonText);
        return $this;
    }

    /**
     * Enable/disable features
     * 
     * @param bool $preview Show preview
     * @param bool $progress Show progress
     * @param bool $fileInfo Show file info
     * @param bool $allowRemove Allow file removal
     * @return self
     */
    public function features(bool $preview = true, bool $progress = true, bool $fileInfo = true, bool $allowRemove = true): self
    {
        return $this->setConfig('show_preview', $preview)
                   ->setConfig('show_progress', $progress)
                   ->setConfig('show_file_info', $fileInfo)
                   ->setConfig('allow_remove', $allowRemove);
    }

    /**
     * Enable multiple file upload
     * 
     * @param int $maxFiles Maximum number of files
     * @return self
     */
    public function multiple(int $maxFiles = 10): self
    {
        return $this->setConfig('max_files', $maxFiles);
    }

    /**
     * Set compact style
     * 
     * @return self
     */
    public function compact(): self
    {
        return $this->setConfig('style', 'compact')
                   ->setConfig('variant', 'button');
    }

    /**
     * Set minimal style
     * 
     * @return self
     */
    public function minimal(): self
    {
        return $this->setConfig('style', 'minimal')
                   ->setConfig('show_file_info', false);
    }
}