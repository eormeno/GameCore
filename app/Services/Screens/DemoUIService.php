<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\Services\UI\Traits\StoresUIState;
use App\Services\UI\Support\UIDiffer;
use App\Services\UI\Components\UIContainer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DemoUIService
{
    use StoresUIState;
    
    /**
     * Get counter value from cache
     * 
     * @return int Current counter value
     */
    private function getCounterValue(): int
    {
        $userId = Auth::check() ? Auth::id() : session()->getId();
        $key = "demo_counter:{$userId}";
        $value = Cache::get($key, 0);
        Log::info('CACHE GET: ' . $key . ' = ' . $value);
        return $value;
    }
    
    /**
     * Set counter value in cache
     * 
     * @param int $value New counter value
     * @return void
     */
    private function setCounterValue(int $value): void
    {
        $userId = Auth::check() ? Auth::id() : session()->getId();
        $key = "demo_counter:{$userId}";
        Cache::put($key, $value, now()->addHours(24));
        
        Log::info('CACHE SET: ' . $key . ' = ' . $value);
        Log::info('CACHE READ BACK: ' . $key . ' = ' . Cache::get($key, 'NOT_SET'));
    }
    
    /**
     * Build base UI structure (required by StoresUIState trait)
     * 
     * Esta UI se genera cada vez que se necesita y se guarda en cache.
     * Los componentes con name tienen IDs determinísticos.
     * 
     * @return UIContainer Base UI structure
     */
    protected function buildBaseUI(): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Demo UI Components');

        // Build UI elements
        $this->buildUIElements($container);

        return $container;
    }
    
    /**
     * Get the demo screen with various UI components
     * 
     * Retorna UI desde cache o regenera si no existe
     *
     * @return array
     */
    public function getDemoScreen(): array
    {
        // Usar cache con auto-regeneración
        return $this->getStoredUI();
    }

    /**
     * Build and add UI elements to the container
     * 
     * @param \App\Services\UI\Components\UIContainer $container
     * @return void
     */
    private function buildUIElements($container): void
    {
        // ========================================
        // DEMO SIMPLIFICADO - SISTEMA REACTIVO
        // ========================================
        
        // Welcome label (con nombre para poder modificarlo)
        $container->add(
            UIBuilder::label('lbl_welcome')
                ->text('🔵 Estado inicial: Presiona "Test Update" para cambiar este texto')
                ->style('info')
        );
        
        // Botón para ACTUALIZAR componente
        $container->add(
            UIBuilder::button('btn_test_update')
                ->label('🔄 Test Update (ACTUALIZAR)')
                ->action('test_action')
                ->icon('star')
                ->style('primary')
                ->variant('filled')
        );

        // Botón para AGREGAR componente
        $container->add(
            UIBuilder::button('btn_test_add')
                ->label('➕ Test Add (AGREGAR)')
                ->action('open_settings')
                ->icon('settings')
                ->style('warning')
                ->variant('filled')
        );

        // Separador visual
        $container->add(
            UIBuilder::label()
                ->text('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
                ->style('default')
        );

        // Contador con botones incrementar/decrementar
        $container->add(
            UIBuilder::label()
                ->text('🔢 Contador Interactivo:')
                ->style('default')
        );

        $counterContainer = UIBuilder::container('counter_container')
            ->layout(LayoutType::HORIZONTAL);

        $counterContainer->add(
            UIBuilder::button('btn_decrement')
                ->label('➖')
                ->action('decrement_counter')
                ->style('danger')
                ->variant('filled')
        );

        // Obtener valor del contador desde session
        $counterValue = $this->getCounterValue();
        $counterStyle = 'primary';
        
        if ($counterValue > 5) {
            $counterStyle = 'success';
        } elseif ($counterValue < 0) {
            $counterStyle = 'danger';
        }

        $counterContainer->add(
            UIBuilder::label('lbl_counter')
                ->text((string) $counterValue)
                ->style($counterStyle)
        );

        $counterContainer->add(
            UIBuilder::button('btn_increment')
                ->label('➕')
                ->action('increment_counter')
                ->style('success')
                ->variant('filled')
        );

        $container->add($counterContainer);

        // Separador visual
        $container->add(
            UIBuilder::label()
                ->text('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━')
                ->style('default')
        );

        $container->add(
            UIBuilder::label()
                ->text('💡 Nuevos componentes aparecerán aquí abajo:')
                ->style('default')
        );

        /* CÓDIGO COMENTADO - DEMOS ADICIONALES
        
        // Section: Buttons
        $container->add(
            UIBuilder::label()
                ->text('Buttons with Different Styles')
                ->style('heading')
        );

        $container->add(
            UIBuilder::button('btn_success')
                ->label('Success Button')
                ->action('test_action')
                ->icon('check')
                ->style('success')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_danger')
                ->label('Danger Button')
                ->action('test_action')
                ->icon('trash')
                ->style('danger')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_warning')
                ->label('Warning Button')
                ->action('test_action')
                ->icon('alert')
                ->style('warning')
                ->variant('filled')
        );

        $container->add(
            UIBuilder::button('btn_disabled')
                ->label('Disabled Button')
                ->action('test_action')
                ->icon('lock')
                ->style('primary')
                ->enabled(false)
        );

        // Section: Horizontal Container with Buttons
        $container->add(
            UIBuilder::label()
                ->text('Horizontal Layout - Button Group')
                ->style('heading')
        );

        $horizontalButtons = UIBuilder::container('horizontal_buttons')
            ->layout(LayoutType::HORIZONTAL);

        $horizontalButtons->add(
            UIBuilder::button('h_btn_1')
                ->label('Action 1')
                ->action('action_1')
                ->style('primary')
        );

        $horizontalButtons->add(
            UIBuilder::button('h_btn_2')
                ->label('Action 2')
                ->action('action_2')
                ->style('success')
        );

        $horizontalButtons->add(
            UIBuilder::button('h_btn_3')
                ->label('Action 3')
                ->action('action_3')
                ->style('danger')
        );

        $container->add($horizontalButtons);

        // Section: Labels
        $container->add(
            UIBuilder::label()
                ->text('Labels with Different Styles')
                ->style('heading')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a default label')
                ->style('default')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a success message')
                ->style('success')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is a warning message')
                ->style('warning')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is an error message')
                ->style('error')
        );

        $container->add(
            UIBuilder::label()
                ->text('This is an info message')
                ->style('info')
        );

        // Section: Nested Container - Form Layout
        $container->add(
            UIBuilder::label()
                ->text('Form Layout - Vertical Container with Form Fields')
                ->style('heading')
        );

        $formContainer = UIBuilder::container('form_container')
            ->layout(LayoutType::VERTICAL)
            ->title('User Registration Form');

        $formContainer->add(
            UIBuilder::input('form_username')
                ->label('Username')
                ->placeholder('Enter your username')
                ->value('')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::input('form_email')
                ->label('Email Address')
                ->placeholder('user@example.com')
                ->type('email')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::input('form_password')
                ->label('Password')
                ->placeholder('Enter your password')
                ->type('password')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::select('form_country')
                ->label('Country')
                ->options([
                    'us' => 'United States',
                    'uk' => 'United Kingdom',
                    'ca' => 'Canada',
                    'mx' => 'Mexico',
                    'es' => 'Spain',
                ])
                ->placeholder('Select your country')
                ->required(true)
        );

        $formContainer->add(
            UIBuilder::checkbox('form_terms')
                ->label('I agree to the terms and conditions')
                ->checked(false)
                ->required(true)
        );

        // Form buttons in horizontal layout
        $formButtons = UIBuilder::container('form_buttons')
            ->layout(LayoutType::HORIZONTAL);

        $formButtons->add(
            UIBuilder::button('form_submit')
                ->label('Submit')
                ->action('submit_form')
                ->style('success')
        );

        $formButtons->add(
            UIBuilder::button('form_cancel')
                ->label('Cancel')
                ->action('cancel_form')
                ->style('danger')
        );

        $formContainer->add($formButtons);

        $container->add($formContainer);
        */ // FIN DE CÓDIGO COMENTADO
    }

    // ============================================================
    // Event Handlers
    // ============================================================
    // Methods that handle UI component events from the frontend.
    // Convention: action "snake_case" → method "onPascalCase"
    // ============================================================

    /**
     * Handle test action event
     * 
     * Triggered by: Primary Button (action: "test_action")
     * Demuestra: Actualización automática de texto en label
     * 
     * @param array $params Event parameters
     * @return array Response with UI updates
     */
    public function onTestAction(array $params): array
    {
        // 1. Obtener UI anterior del cache
        $oldUI = $this->getStoredUI();
        
        // 2. Regenerar UI con cambios
        $container = $this->buildBaseUI();
        
        // 3. Modificar componente específico
        // Cambiar el texto del primer label (Welcome label)
        $welcomeLabel = $container->findByName('lbl_welcome');
        if ($welcomeLabel && method_exists($welcomeLabel, 'text')) {
            /** @var \App\Services\UI\Components\LabelBuilder $welcomeLabel */
            $welcomeLabel->text('¡Botón presionado! Acción ejecutada exitosamente.');
            $welcomeLabel->style('success');
        }
        
        // 4. Guardar nueva versión en cache
        $newUI = $container->toJson();
        $this->storeUI($container);
        
        // 5. Calcular y retornar solo los cambios
        return [
            'message' => 'Test action executed successfully!',
            'ui_update' => UIDiffer::compare($oldUI, $newUI)
        ];
    }

    /**
     * Handle form submission
     * 
     * Example action: "submit_form"
     * 
     * @param array $params Form data
     * @return array Response
     */
    public function onSubmitForm(array $params): array
    {
        // Validate form data
        $username = $params['username'] ?? null;
        $email = $params['email'] ?? null;

        if (empty($username) || empty($email)) {
            return response()->json([
                'error' => 'Username and email are required',
            ], 400)->getData(true);
        }

        // Process form (save to database, send email, etc.)
        // ...

        return [
            'message' => "Form submitted successfully for user: {$username}",
            'data' => [
                'username' => $username,
                'email' => $email,
            ],
        ];
    }

    /**
     * Handle form cancellation
     * 
     * Example action: "cancel_form"
     * 
     * @param array $params Event parameters
     * @return array Response
     */
    public function onCancelForm(array $params): array
    {
        return [
            'message' => 'Form cancelled',
            'redirect' => '/dashboard',
        ];
    }

    /**
     * Handle settings opening
     * 
     * Example action: "open_settings"
     * Demuestra: Agregar nuevo componente dinámicamente
     * 
     * @param array $params Event parameters
     * @return array Response with UI update
     */
    public function onOpenSettings(array $params): array
    {
        $oldUI = $this->getStoredUI();
        
        $container = $this->buildBaseUI();
        
        // Agregar nuevo label al final
        $container->add(
            UIBuilder::label('lbl_settings_' . time())
                ->text('⚙️ Settings panel opened!')
                ->style('warning')
        );
        
        $newUI = $container->toJson();
        $this->storeUI($container);
        
        return [
            'message' => 'Opening settings...',
            'ui_update' => UIDiffer::compare($oldUI, $newUI)
        ];
    }

    /**
     * Handle counter increment
     * 
     * Example action: "increment_counter"
     * Demuestra: Actualizar valor numérico en label
     * 
     * @param array $params Event parameters
     * @return array Response with UI update
     */
    public function onIncrementCounter(array $params): array
    {
        // 1. Incrementar valor en sesión PRIMERO
        $currentValue = $this->getCounterValue();
        $newValue = $currentValue + 1;
        $this->setCounterValue($newValue);
        
        // 2. Forzar regeneración de UI (sin usar cache)
        // El problema es que Cache::remember no regenera si existe
        $this->clearStoredUI();
        $container = $this->buildBaseUI();
        $newUI = $container->toJson();
        
        // 3. Guardar en cache para próxima carga
        $this->storeUI($container);
        
        // 4. Para el diff, solo enviamos el cambio del contador
        // (más eficiente que comparar toda la UI)
        $counterLabel = $container->findByName('lbl_counter');
        $diff = [];
        
        if ($counterLabel) {
            $counterJson = $counterLabel->toJson();
            // toJson() retorna [id => config]
            $counterId = array_key_first($counterJson);
            $counterConfig = $counterJson[$counterId];
            
            $diff[$counterId] = [
                'text' => (string) $newValue,
                'style' => $counterConfig['style']
            ];
        }
        
        Log::info('INCREMENT - Current: ' . $currentValue . ', New: ' . $newValue);
        Log::info('Diff: ' . json_encode($diff));
        
        return [
            'message' => "Counter incremented to {$newValue}",
            'ui_update' => $diff
        ];
    }

    /**
     * Handle counter decrement
     * 
     * Example action: "decrement_counter"
     * Demuestra: Actualizar valor numérico en label
     * 
     * @param array $params Event parameters
     * @return array Response with UI update
     */
    public function onDecrementCounter(array $params): array
    {
        // 1. Decrementar valor en sesión PRIMERO
        $currentValue = $this->getCounterValue();
        $newValue = $currentValue - 1;
        $this->setCounterValue($newValue);
        
        // 2. Forzar regeneración de UI
        $this->clearStoredUI();
        $container = $this->buildBaseUI();
        
        // 3. Guardar en cache
        $this->storeUI($container);
        
        // 4. Crear diff manual del contador
        $counterLabel = $container->findByName('lbl_counter');
        $diff = [];
        
        if ($counterLabel) {
            $counterJson = $counterLabel->toJson();
            // toJson() retorna [id => config]
            $counterId = array_key_first($counterJson);
            $counterConfig = $counterJson[$counterId];
            
            $diff[$counterId] = [
                'text' => (string) $newValue,
                'style' => $counterConfig['style']
            ];
        }
        
        return [
            'message' => "Counter decremented to {$newValue}",
            'ui_update' => $diff
        ];
    }
}