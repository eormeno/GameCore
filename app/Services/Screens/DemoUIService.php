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

        // Section: Inputs
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
}
