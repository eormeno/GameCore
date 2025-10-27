<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Modals\ConfirmDialogService;
use App\Services\UI\Enums\DialogType;
use App\Services\UI\Enums\TimeUnit;

/**
 * Demo Menu Service
 * 
 * Builds the main navigation menu for demo screens
 */
class DemoMenuService extends AbstractUIService
{
    protected function buildBaseUI(...$params): UIContainer
    {
        // Menu doesn't use a container, but AbstractUIService requires this method.
        // Returning empty container - actual menu is built in getUI()
        return UIBuilder::container('_menu_placeholder');
        // Explicación: Este método buildBaseUI es necesario para cumplir con la interfaz
        // de AbstractUIService, pero en este caso no se utiliza para construir el menú real.
        // En su lugar, devolvemos un contenedor vacío llamado '_menu_placeholder' como marcador de posición.
        // El menú real se construye en el método getUI().
    }

    public function getUI(...$params): array
    {
        // Get service ID to receive callbacks
        $serviceId = $this->getServiceComponentId();

        // Build menu using UIBuilder
        $menu = UIBuilder::menuDropdown('main_menu')
            ->parent('menu') // Render in #menu div
            ->callerServiceId($serviceId); // Set service for action callbacks

        // Demos submenu
        $menu->submenu('Demos', '🎮', function($submenu) {
            $submenu->link('Demo UI', '/demo/demo-ui', '🎨');
            $submenu->link('Table Demo', '/demo/table-demo', '📊');
            $submenu->link('Modal Demo', '/demo/modal-demo', '🪟');
            $submenu->link('Form Demo', '/demo/form-demo', '📝');
            $submenu->link('Button Demo', '/demo/button-demo', '🔘');
            $submenu->link('Input Demo', '/demo/input-demo', '⌨️');
            $submenu->link('Select Demo', '/demo/select-demo', '📋');
            $submenu->link('Checkbox Demo', '/demo/checkbox-demo', '☑️');
        });

        $menu->separator();

        // UI Components submenu (future components)
        $menu->submenu('Components', '🧩', function($submenu) {
            $submenu->link('Cards', '/demo/cards', '🃏');
            $submenu->link('Alerts', '/demo/alerts', '⚠️');
            $submenu->link('Tabs', '/demo/tabs', '📑');
            $submenu->separator();
            $submenu->item('Test Error Dialog', 'show_error_dialog', [], '❌');
            $submenu->item('Test Timeout (10s)', 'show_timeout_dialog', ['duration' => 10], '⏱️');
            $submenu->item('Test Timeout (5min)', 'show_timeout_minutes', [], '⏱️');
            $submenu->item('Test Timeout (no button)', 'show_timeout_no_button', [], '⏱️');
        });

        $menu->separator();

        // Settings (with WARNING dialog)
        $menu->item('Settings', 'show_settings_confirm', [], '⚙️');
        
        // About (with INFO dialog)
        $menu->item('About', 'show_about_info', [], 'ℹ️');

        return $menu->build();
    }

    /**
     * Handler for Settings confirmation dialog
     */
    public function onShowSettingsConfirm(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build warning dialog using ConfirmDialogService with DialogType
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::WARNING,
            title: "Configuración",
            message: "¿Quieres resetear la configuración? Esta acción no se puede deshacer.",
            confirmAction: 'reset_settings',
            confirmParams: [],
            cancelAction: 'cancel_settings',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler for cancel button (closes modal)
     */
    public function onCancelSettings(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for reset button - shows success dialog
     */
    public function onResetSettings(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // First close the warning dialog, then show success dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::SUCCESS,
            title: "¡Completado!",
            message: "La configuración ha sido reseteada correctamente.",
            confirmAction: 'close_success_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close success dialog
     */
    public function onCloseSuccessDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for About info dialog
     */
    public function onShowAboutInfo(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build info dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::INFO,
            title: "Acerca de GameCore",
            message: "Sistema de componentes UI v1.0\n\nDesarrollado con Laravel y componentes modulares.\n\nSoporta: Tables, Modals, Forms, Menus y más.",
            confirmAction: 'close_about_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close about dialog
     */
    public function onCloseAboutDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Error dialog demo
     */
    public function onShowErrorDialog(array $params): array
    {
        // Get this service ID to receive the callback
        $serviceId = $this->getServiceComponentId();

        // Build error dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::ERROR,
            title: "Error de conexión",
            message: "No se pudo conectar con el servidor.\n\nPor favor, verifica tu conexión a internet e intenta nuevamente.",
            confirmAction: 'close_error_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close error dialog
     */
    public function onCloseErrorDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Timeout dialog (10 seconds)
     */
    public function onShowTimeoutDialog(array $params): array
    {
        $serviceId = $this->getServiceComponentId();
        $duration = $params['duration'] ?? 10;

        // Build timeout dialog
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Notificación Temporal",
            message: "Este mensaje se autodestruirá en:",
            timeout: $duration,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            confirmAction: 'close_timeout_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler for Timeout dialog (5 minutes)
     */
    public function onShowTimeoutMinutes(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        // Build timeout dialog with minutes
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Sesión Temporal",
            message: "Tu sesión de prueba expirará en:",
            timeout: 5,
            timeUnit: TimeUnit::MINUTES,
            showCountdown: true,
            confirmAction: 'close_timeout_dialog',
            callerServiceId: $serviceId
        );

        return $modalUI;
    }

    /**
     * Handler to close timeout dialog
     */
    public function onCloseTimeoutDialog(array $params): array
    {
        return [
            'action' => 'close_modal',
            'modal_id' => 'confirm_dialog'
        ];
    }

    /**
     * Handler for Timeout dialog without close button (5 seconds)
     */
    public function onShowTimeoutNoButton(array $params): array
    {
        $serviceId = $this->getServiceComponentId();

        // Build timeout dialog without close button
        $confirmService = app(ConfirmDialogService::class);
        $modalUI = $confirmService->getUI(
            type: DialogType::TIMEOUT,
            title: "Auto-cierre",
            message: "Este diálogo se cerrará automáticamente en:",
            timeout: 5,
            timeUnit: TimeUnit::SECONDS,
            showCountdown: true,
            showCloseButton: false, // No mostrar botón de cerrar
            callerServiceId: $serviceId
        );

        return $modalUI;
    }
}
