<?php

use App\Services\UI\UIBuilder;

describe('FormBuilder Integration with GameApps', function () {
    
    test('BBA - can create user settings form', function () {
        $form = UIBuilder::form('bba-settings')
            ->action('/bba/settings')
            ->method('POST')
            ->ajax(true)
            ->horizontalLayout()
            ->labelWidth('180px')
            ->bordered(true)
            ->rounded(true)
            ->padding('large');
        
        // Game Settings Section
        $form->section('Game Settings', function ($section) {
            $section->add(
                UIBuilder::select('difficulty')
                    ->label('Difficulty Level')
                    ->options([
                        ['value' => 'easy', 'label' => 'Easy'],
                        ['value' => 'medium', 'label' => 'Medium'],
                        ['value' => 'hard', 'label' => 'Hard'],
                    ])
                    ->placeholder('Select difficulty')
                    ->required(true)
            );
            
            $section->add(
                UIBuilder::checkbox('auto_save')
                    ->label('Enable Auto-Save')
                    ->checked(true)
            );
            
            $section->add(
                UIBuilder::checkbox('sound_enabled')
                    ->label('Enable Sound Effects')
                    ->checked(true)
            );
        });
        
        // Display Settings Section
        $form->section('Display Settings', function ($section) {
            $section->add(
                UIBuilder::select('theme')
                    ->label('Theme')
                    ->options([
                        ['value' => 'light', 'label' => 'Light'],
                        ['value' => 'dark', 'label' => 'Dark'],
                        ['value' => 'auto', 'label' => 'Auto'],
                    ])
                    ->value('auto')
            );
            
            $section->add(
                UIBuilder::checkbox('animations')
                    ->label('Enable Animations')
                    ->checked(true)
            );
        });
        
        // Privacy Section
        $form->section('Privacy', function ($section) {
            $section->add(
                UIBuilder::checkbox('show_online_status')
                    ->label('Show Online Status')
                    ->checked(false)
                    ->helpText('Let other players see when you are online')
            );
            
            $section->add(
                UIBuilder::checkbox('allow_invites')
                    ->label('Allow Game Invites')
                    ->checked(true)
            );
        });
        
        $form->submitButton('Save Settings', ['style' => 'primary'])
            ->resetButton('Reset to Defaults');
        
        $json = $form->build();
        $formId = $form->getId();
        
        // Verify form structure
        expect($json[$formId]['action'])->toBe('/bba/settings');
        expect($json[$formId]['sections'])->toHaveCount(3);
        expect($json[$formId]['buttons'])->toHaveCount(2);
        
        // Count elements
        $elementTypes = [];
        foreach ($json as $config) {
            if (isset($config['type'])) {
                $type = $config['type'];
                $elementTypes[$type] = ($elementTypes[$type] ?? 0) + 1;
            }
        }
        
        expect($elementTypes['form'])->toBe(1);
        expect($elementTypes['section'])->toBe(3);
        expect($elementTypes['select'])->toBe(2);
        expect($elementTypes['checkbox'])->toBe(5);
    });

    test('BBA - can create player registration form', function () {
        $form = UIBuilder::form('bba-register')
            ->action('/bba/register')
            ->method('POST')
            ->ajax(true)
            ->verticalLayout()
            ->validationMode('onChange')
            ->errorDisplay('inline')
            ->focusOnError(true)
            ->scrollToError(true)
            ->preventMultipleSubmit(true)
            ->showProgress(true)
            ->loadingMessage('Creating your account...')
            ->successMessage('Account created successfully!')
            ->redirectOnSuccess('/bba/lobby');
        
        $form->input('username', 'Username')
            ->placeholder('Choose a username')
            ->required(true)
            ->minlength(3)
            ->maxlength(20)
            ->helpText('3-20 characters, letters and numbers only');
        
        $form->input('email', 'Email Address')
            ->type('email')
            ->placeholder('your@email.com')
            ->required(true)
            ->helpText('We will send a confirmation email');
        
        $form->input('password', 'Password')
            ->type('password')
            ->required(true)
            ->minlength(8)
            ->helpText('At least 8 characters');
        
        $form->input('password_confirm', 'Confirm Password')
            ->type('password')
            ->required(true);
        
        $form->select('country', 'Country')
            ->options([
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'uk', 'label' => 'United Kingdom'],
                ['value' => 'ca', 'label' => 'Canada'],
            ])
            ->searchable(true)
            ->placeholder('Select your country')
            ->required(true);
        
        $form->checkbox('terms', 'I accept the Terms and Conditions')
            ->required(true);
        
        $form->checkbox('newsletter', 'Send me game updates and news')
            ->checked(false);
        
        $form->submitButton('Create Account', ['style' => 'success', 'size' => 'large'])
            ->cancelButton('Cancel', ['onclick' => 'window.location="/bba"']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['action'])->toBe('/bba/register');
        expect($json[$formId]['ajax'])->toBe(true);
        expect($json[$formId]['validation_mode'])->toBe('onChange');
        expect($json[$formId]['loading_message'])->toBe('Creating your account...');
        expect($json[$formId]['redirect_on_success'])->toBe('/bba/lobby');
    });

    test('CNT - can create game lobby configuration form', function () {
        $form = UIBuilder::form('cnt-lobby-config')
            ->action('/cnt/lobby/configure')
            ->method('PUT')
            ->ajax(true)
            ->horizontalLayout()
            ->labelWidth('200px')
            ->bordered(true)
            ->shadow(true)
            ->padding('large');
        
        // Lobby Settings
        $form->fieldset('Lobby Settings', function ($fieldset) {
            $fieldset->add(
                UIBuilder::input('lobby_name')
                    ->label('Lobby Name')
                    ->placeholder('Enter lobby name')
                    ->required(true)
                    ->maxlength(50)
            );
            
            $fieldset->add(
                UIBuilder::input('max_players')
                    ->label('Maximum Players')
                    ->type('number')
                    ->min(2)
                    ->max(10)
                    ->value(4)
                    ->required(true)
            );
            
            $fieldset->add(
                UIBuilder::select('visibility')
                    ->label('Lobby Visibility')
                    ->options([
                        ['value' => 'public', 'label' => 'Public'],
                        ['value' => 'friends', 'label' => 'Friends Only'],
                        ['value' => 'private', 'label' => 'Private (Invite Only)'],
                    ])
                    ->value('public')
                    ->required(true)
            );
            
            $fieldset->add(
                UIBuilder::checkbox('password_protected')
                    ->label('Password Protected')
                    ->asSwitch()
            );
        });
        
        // Game Rules
        $form->fieldset('Game Rules', function ($fieldset) {
            $fieldset->add(
                UIBuilder::select('game_mode')
                    ->label('Game Mode')
                    ->options([
                        ['value' => 'standard', 'label' => 'Standard'],
                        ['value' => 'timed', 'label' => 'Timed'],
                        ['value' => 'custom', 'label' => 'Custom Rules'],
                    ])
                    ->required(true)
            );
            
            $fieldset->add(
                UIBuilder::input('time_limit')
                    ->label('Time Limit (minutes)')
                    ->type('number')
                    ->min(5)
                    ->max(60)
                    ->value(30)
            );
            
            $fieldset->add(
                UIBuilder::checkbox('allow_spectators')
                    ->label('Allow Spectators')
                    ->asSwitch()
                    ->checked(true)
            );
        });
        
        $form->submitButton('Save Configuration')
            ->resetButton('Reset')
            ->cancelButton('Cancel', ['onclick' => 'closeModal()']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        // Verify form uses PUT method with spoofing
        expect($json[$formId]['method'])->toBe('POST'); // HTML only supports GET/POST
        expect($json[$formId]['_method'])->toBe('PUT'); // Method spoofing
        expect($json[$formId]['fieldsets'])->toHaveCount(2);
        expect($json[$formId]['buttons'])->toHaveCount(3);
    });

    test('CNT - can create quick match form', function () {
        $form = UIBuilder::form('cnt-quick-match')
            ->action('/cnt/match/quick')
            ->method('POST')
            ->ajax(true)
            ->inlineLayout()
            ->showProgress(true)
            ->loadingMessage('Finding opponents...')
            ->preventMultipleSubmit(true);
        
        $form->select('skill_level', 'Skill Level')
            ->options([
                ['value' => 'beginner', 'label' => 'Beginner'],
                ['value' => 'intermediate', 'label' => 'Intermediate'],
                ['value' => 'advanced', 'label' => 'Advanced'],
                ['value' => 'expert', 'label' => 'Expert'],
            ])
            ->value('intermediate')
            ->style('primary')
            ->size('medium');
        
        $form->select('region', 'Region')
            ->options([
                ['value' => 'auto', 'label' => 'Auto (Best Ping)'],
                ['value' => 'na', 'label' => 'North America'],
                ['value' => 'eu', 'label' => 'Europe'],
                ['value' => 'asia', 'label' => 'Asia'],
            ])
            ->value('auto')
            ->style('primary')
            ->size('medium');
        
        $form->submitButton('Find Match', ['style' => 'success', 'size' => 'large']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['form_layout'])->toBe('inline');
        expect($json[$formId]['loading_message'])->toBe('Finding opponents...');
        expect($json[$formId]['prevent_multiple_submit'])->toBe(true);
    });

    test('can create form with all field types', function () {
        $form = UIBuilder::form('all-fields')
            ->action('/submit')
            ->method('POST')
            ->verticalLayout()
            ->fieldSpacing('large');
        
        // Input field
        $form->input('text_field', 'Text Input')
            ->placeholder('Enter text');
        
        // Number input
        $form->input('number_field', 'Number Input')
            ->type('number')
            ->min(1)
            ->max(100);
        
        // Email input
        $form->input('email_field', 'Email Input')
            ->type('email');
        
        // Select field
        $form->select('select_field', 'Select Field')
            ->options([
                ['value' => '1', 'label' => 'Option 1'],
                ['value' => '2', 'label' => 'Option 2'],
            ]);
        
        // Checkbox field
        $form->checkbox('checkbox_field', 'Checkbox Field');
        
        // Multiple checkboxes
        $form->checkbox('multi_checkbox')
            ->label('Multiple Choices')
            ->options([
                ['value' => 'a', 'label' => 'Choice A'],
                ['value' => 'b', 'label' => 'Choice B'],
                ['value' => 'c', 'label' => 'Choice C'],
            ]);
        
        $form->submitButton('Submit All');
        
        $json = $form->build();
        
        // Count field types
        $elementTypes = [];
        foreach ($json as $config) {
            if (isset($config['type'])) {
                $type = $config['type'];
                $elementTypes[$type] = ($elementTypes[$type] ?? 0) + 1;
            }
        }
        
        expect($elementTypes['form'])->toBe(1);
        expect($elementTypes['input'])->toBe(3);
        expect($elementTypes['select'])->toBe(1);
        expect($elementTypes['checkbox'])->toBe(2);
    });

    test('form inherits from UIContainer and can use container methods', function () {
        $form = UIBuilder::form('container-test');
        
        $button = UIBuilder::button('test-button')->label('Click Me');
        $label = UIBuilder::label('test-label')->text('Hello World');
        
        $form->add($button);
        $form->add($label);
        
        expect($form->has((string)$button->getId()))->toBe(true);
        expect($form->has((string)$label->getId()))->toBe(true);
        
        $found = $form->find((string)$button->getId());
        expect($found)->not->toBeNull();
        expect($found->getId())->toBe($button->getId());
        
        $children = $form->getChildren();
        expect($children)->toHaveCount(2);
    });
});
