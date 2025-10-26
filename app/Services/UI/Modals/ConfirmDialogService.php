<?php

namespace App\Services\UI\Modals;

use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\UIBuilder;

/**
 * Confirm Dialog Service
 * 
 * Helper service to generate confirmation modals.
 * Does not inherit from AbstractUIService as it's a utility service.
 */
class ConfirmDialogService
{
    /**
     * Build a confirmation dialog UI
     * 
     * @param mixed ...$params Parameters:
     *   - title: Modal title
     *   - message: Confirmation message
     *   - icon: Icon type ('question', 'info', 'warning', 'error', 'success')
     *   - confirmAction: Action name for confirm button
     *   - confirmParams: Additional parameters for confirm action
     *   - confirmLabel: Label for confirm button (default: 'Confirmar')
     *   - cancelAction: Action name for cancel button (default: 'close_modal')
     *   - cancelLabel: Label for cancel button (default: 'Cancelar')
     *   - callerServiceId: ID of the service that opened the modal
     * 
     * @return array UI configuration array
     */
    public function getUI(...$params): array
    {
        // Extract parameters
        $title = $params['title'] ?? 'Confirmar';
        $message = $params['message'] ?? '¿Está seguro?';
        $icon = $params['icon'] ?? null;
        $confirmAction = $params['confirmAction'] ?? 'confirm';
        $confirmParams = $params['confirmParams'] ?? [];
        $confirmLabel = $params['confirmLabel'] ?? 'Confirmar';
        $cancelAction = $params['cancelAction'] ?? 'close_modal';
        $cancelLabel = $params['cancelLabel'] ?? 'Cancelar';
        $callerServiceId = $params['callerServiceId'] ?? null;

        // Build container - use 'modal' as parent to indicate it should be rendered in the modal overlay
        $container = UIBuilder::container('confirm_dialog')
            ->parent('modal')
            ->layout(LayoutType::VERTICAL)
            ->shadow(0) // No shadow since modal already has shadow
            ->rounded(4) // Subtle 4px border radius
            ->padding(0) // No padding
            ->gap(8) // Space between elements
            ->centerContent(); // Center content horizontally

        // Icon (if specified)
        if ($icon) {
            $iconEmoji = $this->getIconEmoji($icon);
            $container->add(
                UIBuilder::label('icon')
                    ->text($iconEmoji)
                    ->fontSize(48) // Large emoji (48px)
            );
        }

        // Title
        $container->add(
            UIBuilder::label('title')
                ->text($title)
                ->style('h3')
        );

        // Message
        $container->add(
            UIBuilder::label('message')
                ->text($message)
        );

        // Buttons container (horizontal layout)
        $buttonsContainer = UIBuilder::container('buttons')
            ->layout(LayoutType::HORIZONTAL)
            ->shadow(0) // No shadow on buttons container
            ->rounded(0) // No border radius on buttons container
            ->padding(0) // No padding
            ->gap(8) // Space between buttons
            ->centerContent(); // Center buttons horizontally

        // Cancel button
        $buttonsContainer->add(
            UIBuilder::button('btn_cancel')
                ->label($cancelLabel)
                ->style('secondary')
                ->action($cancelAction, [
                    '_caller_service_id' => $callerServiceId
                ])
        );

        // Confirm button
        $buttonsContainer->add(
            UIBuilder::button('btn_confirm')
                ->label($confirmLabel)
                ->style('danger')
                ->action($confirmAction, array_merge($confirmParams, [
                    '_caller_service_id' => $callerServiceId
                ]))
        );

        $container->add($buttonsContainer);

        return $container->build();
    }

    /**
     * Get emoji character for the specified icon type
     */
    private function getIconEmoji(string $icon): string
    {
        return match($icon) {
            'question' => '❓',
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'error' => '❌',
            'success' => '✅',
            default => '❓'
        };
    }
}
