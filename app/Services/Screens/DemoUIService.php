<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;

class DemoUIService
{
    /**
     * Get the demo screen with various UI components
     *
     * @return array
     */
    public function getDemoScreen(): array
    {
        $container = UIBuilder::container()
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Demo UI Components');

        // Build UI elements
        $this->buildUIElements($container);

        return $container->toJson();
    }

    /**
     * Build and add UI elements to the container
     * 
     * @param \App\Services\UI\Components\UIContainer $container
     * @return void
     */
    private function buildUIElements($container): void
    {
        // Section: Buttons
        $container->add(
            UIBuilder::label()
                ->text('Buttons with Different Styles')
                ->style('heading')
        );

        $container->add(
            UIBuilder::button('btn_primary')
                ->label('Primary Button')
                ->action('test_action')
                ->icon('star')
                ->style('primary')
                ->variant('filled')
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

        // Section: Input Fields
        $container->add(
            UIBuilder::label()
                ->text('Input Fields')
                ->style('heading')
        );

        $container->add(
            UIBuilder::input('input_text')
                ->label('Username')
                ->placeholder('Enter your username')
                ->value('')
                ->required(true)
        );

        $container->add(
            UIBuilder::input('input_email')
                ->label('Email Address')
                ->placeholder('user@example.com')
                ->type('email')
                ->required(true)
        );

        $container->add(
            UIBuilder::input('input_password')
                ->label('Password')
                ->placeholder('Enter your password')
                ->type('password')
                ->required(true)
        );

        $container->add(
            UIBuilder::input('input_disabled')
                ->label('Disabled Input')
                ->placeholder('This field is disabled')
                ->value('Cannot edit this')
                ->disabled(true)
        );

        // Section: Two Column Layout
        $container->add(
            UIBuilder::label()
                ->text('Two Column Layout - Side by Side Containers')
                ->style('heading')
        );

        $twoColumnContainer = UIBuilder::container('two_columns')
            ->layout(LayoutType::HORIZONTAL);

        // Left Column
        $leftColumn = UIBuilder::container('left_column')
            ->layout(LayoutType::VERTICAL)
            ->title('Left Panel');

        $leftColumn->add(
            UIBuilder::label()
                ->text('This is the left panel')
                ->style('info')
        );

        $leftColumn->add(
            UIBuilder::button('left_action')
                ->label('Left Action')
                ->action('left_action')
                ->style('primary')
        );

        $leftColumn->add(
            UIBuilder::input('left_input')
                ->label('Left Input')
                ->placeholder('Type something...')
        );

        // Right Column
        $rightColumn = UIBuilder::container('right_column')
            ->layout(LayoutType::VERTICAL)
            ->title('Right Panel');

        $rightColumn->add(
            UIBuilder::label()
                ->text('This is the right panel')
                ->style('success')
        );

        $rightColumn->add(
            UIBuilder::button('right_action')
                ->label('Right Action')
                ->action('right_action')
                ->style('success')
        );

        $rightColumn->add(
            UIBuilder::checkbox('right_checkbox')
                ->label('Right Checkbox')
                ->checked(true)
        );

        $twoColumnContainer->add($leftColumn);
        $twoColumnContainer->add($rightColumn);

        $container->add($twoColumnContainer);

        // Section: Selects
        $container->add(
            UIBuilder::label()
                ->text('Select Dropdowns')
                ->style('heading')
        );

        $container->add(
            UIBuilder::select('select_country')
                ->label('Select Country')
                ->options([
                    'us' => 'United States',
                    'uk' => 'United Kingdom',
                    'ca' => 'Canada',
                    'mx' => 'Mexico',
                    'es' => 'Spain',
                ])
                ->value('us')
                ->required(true)
        );

        $container->add(
            UIBuilder::select('select_role')
                ->label('Select Role')
                ->options([
                    'admin' => 'Administrator',
                    'moderator' => 'Moderator',
                    'user' => 'User',
                    'guest' => 'Guest',
                ])
                ->placeholder('Choose a role...')
        );

        $container->add(
            UIBuilder::select('select_disabled')
                ->label('Disabled Select')
                ->options([
                    'option1' => 'Option 1',
                    'option2' => 'Option 2',
                ])
                ->value('option1')
                ->disabled(true)
        );

        // Section: Complex Nested Layout
        $container->add(
            UIBuilder::label()
                ->text('Complex Nested Layout - Dashboard Example')
                ->style('heading')
        );

        $dashboardContainer = UIBuilder::container('dashboard')
            ->layout(LayoutType::VERTICAL)
            ->title('Dashboard');

        // Dashboard Header with buttons
        $dashboardHeader = UIBuilder::container('dashboard_header')
            ->layout(LayoutType::HORIZONTAL);

        $dashboardHeader->add(
            UIBuilder::label()
                ->text('Welcome back, User!')
                ->style('info')
        );

        $dashboardHeader->add(
            UIBuilder::button('dashboard_settings')
                ->label('Settings')
                ->action('open_settings')
                ->style('primary')
        );

        $dashboardHeader->add(
            UIBuilder::button('dashboard_logout')
                ->label('Logout')
                ->action('logout')
                ->style('danger')
        );

        $dashboardContainer->add($dashboardHeader);

        // Dashboard Content - 3 columns
        $dashboardContent = UIBuilder::container('dashboard_content')
            ->layout(LayoutType::HORIZONTAL);

        // Stats Panel 1
        $stats1 = UIBuilder::container('stats_1')
            ->layout(LayoutType::VERTICAL)
            ->title('Total Users');

        $stats1->add(
            UIBuilder::label()
                ->text('1,234')
                ->style('success')
        );

        $stats1->add(
            UIBuilder::label()
                ->text('+15% this month')
                ->style('default')
        );

        // Stats Panel 2
        $stats2 = UIBuilder::container('stats_2')
            ->layout(LayoutType::VERTICAL)
            ->title('Active Games');

        $stats2->add(
            UIBuilder::label()
                ->text('456')
                ->style('info')
        );

        $stats2->add(
            UIBuilder::label()
                ->text('+8% this month')
                ->style('default')
        );

        // Stats Panel 3
        $stats3 = UIBuilder::container('stats_3')
            ->layout(LayoutType::VERTICAL)
            ->title('Revenue');

        $stats3->add(
            UIBuilder::label()
                ->text('$12,345')
                ->style('warning')
        );

        $stats3->add(
            UIBuilder::label()
                ->text('+22% this month')
                ->style('default')
        );

        $dashboardContent->add($stats1);
        $dashboardContent->add($stats2);
        $dashboardContent->add($stats3);

        $dashboardContainer->add($dashboardContent);

        $container->add($dashboardContainer);

        // Section: Checkboxes
        $container->add(
            UIBuilder::label()
                ->text('Checkboxes')
                ->style('heading')
        );

        $container->add(
            UIBuilder::checkbox('check_terms')
                ->label('I accept the terms and conditions')
                ->checked(false)
                ->required(true)
        );

        $container->add(
            UIBuilder::checkbox('check_newsletter')
                ->label('Subscribe to newsletter')
                ->checked(true)
        );

        $container->add(
            UIBuilder::checkbox('check_notifications')
                ->label('Enable notifications')
                ->checked(false)
        );

        $container->add(
            UIBuilder::checkbox('check_disabled')
                ->label('This checkbox is disabled')
                ->checked(true)
                ->disabled(true)
        );
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
     * 
     * @param array $params Event parameters
     * @return array Response
     */
    public function onTestAction(array $params): array
    {
        return [
            'success' => true,
            'message' => 'Test action executed successfully!',
            'timestamp' => now()->toIso8601String(),
            'params' => $params,
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
            return [
                'success' => false,
                'message' => 'Username and email are required',
            ];
        }

        // Process form (save to database, send email, etc.)
        // ...

        return [
            'success' => true,
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
            'success' => true,
            'message' => 'Form cancelled',
            'redirect' => '/dashboard',
        ];
    }

    /**
     * Handle settings opening
     * 
     * Example action: "open_settings"
     * 
     * @param array $params Event parameters
     * @return array Response with UI update
     */
    public function onOpenSettings(array $params): array
    {
        // Could return updated UI components here
        return [
            'success' => true,
            'message' => 'Opening settings...',
            'ui_update' => [
                // New UI components to render
                'modal' => [
                    'type' => 'modal',
                    'title' => 'Settings',
                    'content' => 'Settings panel content here...',
                ],
            ],
        ];
    }
}

