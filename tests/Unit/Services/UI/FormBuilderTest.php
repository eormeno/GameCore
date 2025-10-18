<?php

use App\Services\UI\UIBuilder;
use App\Services\UI\Components\FormBuilder;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\SelectBuilder;
use App\Services\UI\Components\CheckboxBuilder;

describe('FormBuilder', function () {
    
    test('can create a basic form', function () {
        $form = UIBuilder::form('user-form');
        
        expect($form)->toBeInstanceOf(FormBuilder::class);
        expect($form->getId())->toBeInt();
        expect($form->getType())->toBe('form');
    });

    test('can set form properties', function () {
        $form = UIBuilder::form('user-form')
            ->action('/api/users')
            ->method('POST')
            ->ajax(true)
            ->validate(true);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['action'])->toBe('/api/users');
        expect($json[$formId]['method'])->toBe('POST');
        expect($json[$formId]['ajax'])->toBe(true);
        expect($json[$formId]['validate'])->toBe(true);
    });

    test('can set HTTP method and handle PUT/PATCH/DELETE', function () {
        $form = UIBuilder::form('edit-form')
            ->action('/api/users/1')
            ->method('PUT');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['method'])->toBe('POST'); // HTML only supports GET/POST
        expect($json[$formId]['_method'])->toBe('PUT'); // Laravel method spoofing
    });

    test('can configure form layout', function () {
        $form = UIBuilder::form('horizontal-form')
            ->horizontalLayout()
            ->labelWidth('150px')
            ->fieldWidth('300px');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['form_layout'])->toBe('horizontal');
        expect($json[$formId]['label_position'])->toBe('left');
        expect($json[$formId]['label_width'])->toBe('150px');
        expect($json[$formId]['field_width'])->toBe('300px');
    });

    test('can configure validation', function () {
        $form = UIBuilder::form('validated-form')
            ->validationMode('onChange')
            ->errorDisplay('summary')
            ->errorSummaryTitle('Please fix these errors:')
            ->focusOnError(true)
            ->scrollToError(true);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['validation_mode'])->toBe('onChange');
        expect($json[$formId]['error_display'])->toBe('summary');
        expect($json[$formId]['error_summary_title'])->toBe('Please fix these errors:');
        expect($json[$formId]['focus_on_error'])->toBe(true);
        expect($json[$formId]['scroll_to_error'])->toBe(true);
    });

    test('can add submit, reset, and cancel buttons', function () {
        $form = UIBuilder::form('buttons-form')
            ->submitButton('Save')
            ->resetButton('Clear')
            ->cancelButton('Cancel', ['onclick' => 'history.back()']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['buttons'])->toHaveCount(3);
        expect($json[$formId]['buttons'][0]['type'])->toBe('submit');
        expect($json[$formId]['buttons'][0]['label'])->toBe('Save');
        expect($json[$formId]['buttons'][1]['type'])->toBe('reset');
        expect($json[$formId]['buttons'][2]['type'])->toBe('button');
    });

    test('can configure submission behavior', function () {
        $form = UIBuilder::form('submission-form')
            ->preventMultipleSubmit(true)
            ->showProgress(true)
            ->loadingMessage('Processing...')
            ->successMessage('Saved successfully!')
            ->errorMessage('Failed to save')
            ->redirectOnSuccess('/dashboard')
            ->resetOnSuccess(false);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['prevent_multiple_submit'])->toBe(true);
        expect($json[$formId]['show_progress'])->toBe(true);
        expect($json[$formId]['loading_message'])->toBe('Processing...');
        expect($json[$formId]['success_message'])->toBe('Saved successfully!');
        expect($json[$formId]['error_message'])->toBe('Failed to save');
        expect($json[$formId]['redirect_on_success'])->toBe('/dashboard');
        expect($json[$formId]['reset_on_success'])->toBe(false);
    });

    test('can configure security features', function () {
        $form = UIBuilder::form('secure-form')
            ->csrfToken('abc123')
            ->csrfField('_token')
            ->honeypot(true, '_gotcha');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['csrf_token'])->toBe('abc123');
        expect($json[$formId]['csrf_field'])->toBe('_token');
        expect($json[$formId]['honeypot'])->toBe(true);
        expect($json[$formId]['honeypot_field'])->toBe('_gotcha');
    });

    test('can configure auto-save', function () {
        $form = UIBuilder::form('autosave-form')
            ->autoSave(true, 5000);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['auto_save'])->toBe(true);
        expect($json[$formId]['auto_save_delay'])->toBe(5000);
    });

    test('can configure file upload support', function () {
        $form = UIBuilder::form('upload-form')
            ->multipart(true)
            ->maxFileSize(10485760) // 10MB
            ->allowedFileTypes(['jpg', 'png', 'pdf']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($form->hasFileUploads())->toBe(true);
        expect($json[$formId]['encoding'])->toBe('multipart/form-data');
        expect($json[$formId]['max_file_size'])->toBe(10485760);
        expect($json[$formId]['allowed_file_types'])->toBe(['jpg', 'png', 'pdf']);
    });

    test('can configure styling', function () {
        $form = UIBuilder::form('styled-form')
            ->bordered(true)
            ->shadow(true)
            ->rounded(true)
            ->padding('large')
            ->backgroundColor('#f5f5f5')
            ->fieldSpacing('medium')
            ->sectionSpacing('large')
            ->condensed(false);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['bordered'])->toBe(true);
        expect($json[$formId]['shadow'])->toBe(true);
        expect($json[$formId]['rounded'])->toBe(true);
        expect($json[$formId]['padding'])->toBe('large');
        expect($json[$formId]['background_color'])->toBe('#f5f5f5');
        expect($json[$formId]['field_spacing'])->toBe('medium');
        expect($json[$formId]['section_spacing'])->toBe('large');
    });

    test('can configure event handlers', function () {
        $form = UIBuilder::form('events-form')
            ->onSubmit('handleSubmit')
            ->onReset('handleReset')
            ->onChange('handleChange')
            ->beforeSubmit('beforeSubmit')
            ->afterSubmit('afterSubmit');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['on_submit'])->toBe('handleSubmit');
        expect($json[$formId]['on_reset'])->toBe('handleReset');
        expect($json[$formId]['on_change'])->toBe('handleChange');
        expect($json[$formId]['before_submit'])->toBe('beforeSubmit');
        expect($json[$formId]['after_submit'])->toBe('afterSubmit');
    });

    test('can add input fields to form', function () {
        $form = UIBuilder::form('input-form');
        
        $form->input('username', 'Username')
            ->type('text')
            ->placeholder('Enter username')
            ->required(true);
        
        $form->input('email', 'Email')
            ->type('email')
            ->required(true);
        
        $json = $form->build();
        
        expect(count($json))->toBe(3); // form + 2 inputs
        
        // Verify inputs are in the flat JSON structure
        $hasUsername = false;
        $hasEmail = false;
        
        foreach ($json as $id => $config) {
            if (isset($config['type']) && $config['type'] === 'input') {
                if (isset($config['label']) && $config['label'] === 'Username') {
                    $hasUsername = true;
                }
                if (isset($config['label']) && $config['label'] === 'Email') {
                    $hasEmail = true;
                }
            }
        }
        
        expect($hasUsername)->toBe(true);
        expect($hasEmail)->toBe(true);
    });

    test('can add select fields to form', function () {
        $form = UIBuilder::form('select-form');
        
        $form->select('country', 'Country')
            ->options([
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'uk', 'label' => 'United Kingdom'],
            ])
            ->required(true);
        
        $json = $form->build();
        
        $hasSelect = false;
        
        foreach ($json as $id => $config) {
            if (isset($config['type']) && $config['type'] === 'select') {
                $hasSelect = true;
                expect($config['label'])->toBe('Country');
                expect($config['required'])->toBe(true);
            }
        }
        
        expect($hasSelect)->toBe(true);
    });

    test('can add checkbox fields to form', function () {
        $form = UIBuilder::form('checkbox-form');
        
        $form->checkbox('terms', 'Accept Terms')
            ->checked(false)
            ->required(true);
        
        $json = $form->build();
        
        $hasCheckbox = false;
        
        foreach ($json as $id => $config) {
            if (isset($config['type']) && $config['type'] === 'checkbox') {
                $hasCheckbox = true;
                expect($config['label'])->toBe('Accept Terms');
                expect($config['required'])->toBe(true);
            }
        }
        
        expect($hasCheckbox)->toBe(true);
    });

    test('can create fieldsets', function () {
        $form = UIBuilder::form('fieldset-form');
        
        $form->fieldset('Personal Information', function ($fieldset) {
            $fieldset->add(
                (new InputBuilder('first_name'))->label('First Name')
            );
            $fieldset->add(
                (new InputBuilder('last_name'))->label('Last Name')
            );
        });
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['fieldsets'])->toHaveCount(1);
        expect($json[$formId]['fieldsets'][0]['legend'])->toBe('Personal Information');
        
        // Verify fieldset exists in flat JSON
        $hasFieldset = false;
        
        foreach ($json as $id => $config) {
            if (isset($config['type']) && $config['type'] === 'fieldset') {
                $hasFieldset = true;
                expect($config['legend'])->toBe('Personal Information');
            }
        }
        
        expect($hasFieldset)->toBe(true);
    });

    test('can create sections', function () {
        $form = UIBuilder::form('section-form');
        
        $form->section('Account Details', function ($section) {
            $section->add(
                (new InputBuilder('username'))->label('Username')
            );
            $section->add(
                (new InputBuilder('password'))->label('Password')->type('password')
            );
        });
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['sections'])->toHaveCount(1);
        expect($json[$formId]['sections'][0]['title'])->toBe('Account Details');
        
        // Verify section exists in flat JSON
        $hasSection = false;
        
        foreach ($json as $id => $config) {
            if (isset($config['type']) && $config['type'] === 'section') {
                $hasSection = true;
                expect($config['title'])->toBe('Account Details');
            }
        }
        
        expect($hasSection)->toBe(true);
    });

    test('can build complex form with multiple fields and sections', function () {
        $form = UIBuilder::form('complex-form')
            ->action('/api/users')
            ->method('POST')
            ->horizontalLayout()
            ->bordered(true)
            ->rounded(true)
            ->padding('large');
        
        $form->section('Personal Information', function ($section) {
            $section->add(
                (new InputBuilder('first_name'))
                    ->label('First Name')
                    ->required(true)
                    ->placeholder('John')
            );
            $section->add(
                (new InputBuilder('last_name'))
                    ->label('Last Name')
                    ->required(true)
                    ->placeholder('Doe')
            );
        });
        
        $form->section('Contact Information', function ($section) {
            $section->add(
                (new InputBuilder('email'))
                    ->label('Email')
                    ->type('email')
                    ->required(true)
            );
            $section->add(
                (new InputBuilder('phone'))
                    ->label('Phone')
                    ->type('tel')
            );
        });
        
        $form->select('country', 'Country')
            ->options([
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'uk', 'label' => 'United Kingdom'],
            ])
            ->required(true);
        
        $form->checkbox('newsletter', 'Subscribe to newsletter');
        
        $form->submitButton('Create Account')
            ->cancelButton('Cancel');
        
        $json = $form->build();
        $formId = $form->getId();
        
        // Verify form configuration
        expect($json[$formId]['action'])->toBe('/api/users');
        expect($json[$formId]['method'])->toBe('POST');
        expect($json[$formId]['form_layout'])->toBe('horizontal');
        expect($json[$formId]['bordered'])->toBe(true);
        expect($json[$formId]['rounded'])->toBe(true);
        
        // Verify sections
        expect($json[$formId]['sections'])->toHaveCount(2);
        
        // Verify buttons
        expect($json[$formId]['buttons'])->toHaveCount(2);
        
        // Count all elements in flat JSON
        $elementTypes = [];
        foreach ($json as $config) {
            if (isset($config['type'])) {
                $type = $config['type'];
                $elementTypes[$type] = ($elementTypes[$type] ?? 0) + 1;
            }
        }
        
        expect($elementTypes['form'])->toBe(1);
        expect($elementTypes['section'])->toBe(2);
        expect($elementTypes['input'])->toBe(4);
        expect($elementTypes['select'])->toBe(1);
        expect($elementTypes['checkbox'])->toBe(1);
    });

    test('can configure required and optional indicators', function () {
        $form = UIBuilder::form('indicators-form')
            ->showRequiredIndicator(true)
            ->showOptionalIndicator(true);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['show_required_indicator'])->toBe(true);
        expect($json[$formId]['show_optional_indicator'])->toBe(true);
        expect($json[$formId]['required_indicator'])->toBe('*');
        expect($json[$formId]['optional_indicator'])->toBe('(optional)');
    });

    test('can configure submit button position and style', function () {
        $form = UIBuilder::form('button-style-form')
            ->submitPosition('center')
            ->submitStyle('success')
            ->submitSize('large')
            ->fullWidthButtons(true);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['submit_position'])->toBe('center');
        expect($json[$formId]['submit_style'])->toBe('success');
        expect($json[$formId]['submit_size'])->toBe('large');
        expect($json[$formId]['full_width_buttons'])->toBe(true);
    });

    test('can add validation rules', function () {
        $form = UIBuilder::form('validation-form');
        
        $form->addValidationRule('email', 'required', null, 'Email is required');
        $form->addValidationRule('email', 'email', null, 'Must be a valid email');
        $form->addValidationRule('age', 'min', 18, 'Must be at least 18');
        $form->addValidationRule('age', 'max', 100, 'Must be less than 100');
        
        $rules = $form->getValidationRules();
        
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('age');
        expect($rules['email'])->toHaveCount(2);
        expect($rules['age'])->toHaveCount(2);
        expect($rules['email'][0]['rule'])->toBe('required');
        expect($rules['age'][0]['value'])->toBe(18);
    });

    test('can set custom error messages', function () {
        $form = UIBuilder::form('error-form');
        
        $form->setErrorMessage('username', 'Username is already taken');
        $form->setErrorMessage('email', 'Invalid email format');
        
        $messages = $form->getErrorMessages();
        
        expect($messages)->toHaveKey('username');
        expect($messages)->toHaveKey('email');
        expect($messages['username'])->toBe('Username is already taken');
        expect($messages['email'])->toBe('Invalid email format');
    });

    test('can configure accessibility features', function () {
        $form = UIBuilder::form('accessible-form')
            ->ariaLabel('User registration form')
            ->ariaDescribedBy('form-help-text');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['aria_label'])->toBe('User registration form');
        expect($json[$formId]['aria_describedby'])->toBe('form-help-text');
    });

    test('can disable and make readonly', function () {
        $form = UIBuilder::form('disabled-form')
            ->disabled(true);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['disabled'])->toBe(true);
        
        $form2 = UIBuilder::form('readonly-form')
            ->readonly(true);
        
        $json2 = $form2->build();
        $form2Id = $form2->getId();
        
        expect($json2[$form2Id]['readonly'])->toBe(true);
    });

    test('can add custom class and styles', function () {
        $form = UIBuilder::form('custom-form')
            ->customClass('custom-form-class')
            ->customStyle('border: 2px solid red;')
            ->dataAttributes(['test' => 'value', 'foo' => 'bar']);
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['custom_class'])->toBe('custom-form-class');
        expect($json[$formId]['custom_style'])->toBe('border: 2px solid red;');
        expect($json[$formId]['data_attributes'])->toBe(['test' => 'value', 'foo' => 'bar']);
    });

    test('can configure confirmation before submit', function () {
        $form = UIBuilder::form('confirm-form')
            ->confirmBeforeSubmit('Are you sure you want to submit?');
        
        $json = $form->build();
        $formId = $form->getId();
        
        expect($json[$formId]['confirm_before_submit'])->toBe('Are you sure you want to submit?');
    });

    test('throws exception for invalid validation mode', function () {
        $form = UIBuilder::form('invalid-form');
        
        expect(fn() => $form->validationMode('invalid'))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('throws exception for invalid error display', function () {
        $form = UIBuilder::form('invalid-form');
        
        expect(fn() => $form->errorDisplay('invalid'))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('throws exception for invalid form method', function () {
        $form = UIBuilder::form('invalid-form');
        
        expect(fn() => $form->method('INVALID'))
            ->toThrow(\InvalidArgumentException::class);
    });

    test('validation rules and error messages are included in JSON', function () {
        $form = UIBuilder::form('validation-json-form');
        
        $form->addValidationRule('email', 'required');
        $form->setErrorMessage('email', 'Email is required');
        
        $json = $form->toJson();
        $formId = $form->getId();
        
        expect($json[$formId])->toHaveKey('validation_rules');
        expect($json[$formId])->toHaveKey('error_messages');
        expect($json[$formId]['validation_rules'])->toHaveKey('email');
        expect($json[$formId]['error_messages'])->toHaveKey('email');
    });
});
