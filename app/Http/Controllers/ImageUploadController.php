<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    /**
     * Handle image upload from UI components
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // Validate the upload
            $request->validate([
                'file' => 'required|file|image|max:10240', // 10MB max
                'storage_path' => 'string',
                'prefix' => 'string|nullable',
                'component_id' => 'string|required',
                'callback_action' => 'string|nullable',
                '_caller_service_id' => 'string|nullable'
            ]);

            $file = $request->file('file');
            $storagePath = $request->input('storage_path', 'uploads');
            $prefix = $request->input('prefix');
            $componentId = $request->input('component_id');
            $callbackAction = $request->input('callback_action');
            $callerServiceId = $request->input('_caller_service_id');

            // Generate file name
            $fileName = $this->generateFileName($file, $prefix);
            $fullPath = $storagePath . '/' . $fileName;

            // Store the file
            $path = $file->storeAs($storagePath, $fileName, 'public');
            
            if (!$path) {
                return response()->json([
                    'error' => 'Failed to store file'
                ], 500);
            }

            // Get file info
            $fileInfo = [
                'original_name' => $file->getClientOriginalName(),
                'file_name' => $fileName,
                'path' => $path,
                'url' => Storage::url($path),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'component_id' => $componentId
            ];

            // Call backend callback if specified
            if ($callbackAction && $callerServiceId) {
                $this->callServiceCallback($callerServiceId, $callbackAction, $fileInfo, $request);
            }

            Log::info('Image uploaded successfully', [
                'file' => $fileName,
                'path' => $path,
                'component' => $componentId
            ]);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => Storage::url($path),
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'component' => $request->input('component_id')
            ]);

            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a unique file name with optional prefix
     */
    private function generateFileName($file, ?string $prefix = null): string
    {
        $extension = $file->getClientOriginalExtension();
        $baseName = $prefix ?: 'upload';
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return "{$baseName}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Call the backend service callback method
     */
    private function callServiceCallback(string $serviceId, string $action, array $fileInfo, Request $request): void
    {
        try {
            // This would integrate with your existing UI event system
            // For now, we'll just log the callback
            Log::info('Upload callback triggered', [
                'service_id' => $serviceId,
                'action' => $action,
                'file_info' => $fileInfo
            ]);

            // TODO: Integrate with UIEventController to call the service method
            // $eventController = app(UIEventController::class);
            // $eventController->callServiceMethod($serviceId, $action, ['file_info' => $fileInfo]);

        } catch (\Exception $e) {
            Log::warning('Upload callback failed', [
                'service_id' => $serviceId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle UI callback events (from JavaScript components)
     */
    public function handleCallback(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'component_id' => 'required',
                'action' => 'required|string',
                'parameters' => 'array'
            ]);

            $componentId = $request->input('component_id');
            $action = $request->input('action');
            $parameters = $request->input('parameters', []);

            Log::info('UI Callback received', [
                'component_id' => $componentId,
                'action' => $action,
                'parameters' => $parameters
            ]);

            // Handle different callback actions
            $response = $this->processCallback($action, $parameters, $componentId);

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Callback handling failed', [
                'error' => $e->getMessage(),
                'component_id' => $request->input('component_id')
            ]);

            return response()->json([
                'error' => 'Callback failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process the callback action
     */
    private function processCallback(string $action, array $parameters, $componentId): array
    {
        switch ($action) {
            case 'handle_basic_upload':
                return [
                    'message' => '✅ Imagen básica subida correctamente!',
                    'data' => [
                        'files_uploaded' => $parameters['files_count'] ?? 1,
                        'component' => 'basic_upload'
                    ]
                ];

            case 'handle_multiple_upload':
                $count = $parameters['files_count'] ?? 1;
                return [
                    'message' => "✅ {$count} imágenes subidas correctamente!",
                    'data' => [
                        'files_uploaded' => $count,
                        'component' => 'multiple_upload'
                    ]
                ];

            case 'handle_compact_upload':
                return [
                    'message' => '✅ Avatar actualizado correctamente!',
                    'data' => [
                        'files_uploaded' => 1,
                        'component' => 'compact_upload'
                    ]
                ];

            case 'handle_compressed_upload':
                return [
                    'message' => '✅ Imagen comprimida subida correctamente!',
                    'data' => [
                        'files_uploaded' => 1,
                        'component' => 'compressed_upload',
                        'compression_applied' => true
                    ]
                ];

            default:
                return [
                    'message' => '✅ Archivos procesados correctamente!',
                    'data' => ['action' => $action]
                ];
        }
    }

    /**
     * Delete uploaded file
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'path' => 'required|string'
            ]);

            $path = $request->input('path');
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }

            return response()->json([
                'error' => 'File not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'path' => $request->input('path'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }
}