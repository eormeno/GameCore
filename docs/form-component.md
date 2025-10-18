# FormBuilder Component

## Overview

`FormBuilder` is a comprehensive form component that extends `UIContainer` and provides extensive functionality for creating modern, feature-rich web forms with validation, AJAX submission, file uploads, and much more.

## Basic Usage

```php
use App\Services\UI\UIBuilder;

$form = UIBuilder::form('user-form')
    ->action('/api/users')
    ->method('POST')
    ->ajax(true);
```

## Key Features

### 1. Form Configuration

#### Action and Method
```php
$form->action('/api/users')
    ->method('POST');  // GET, POST, PUT, PATCH, DELETE

// For PUT/PATCH/DELETE, Laravel method spoofing is automatically added
$form->method('PUT');  // Adds hidden _method field
```

#### Submission Modes
```php
$form->ajax(true);              // Submit via AJAX
$form->ajax(false);             // Standard form submission
$form->preventMultipleSubmit(); // Prevent double-submission
```

### 2. Form Layouts

#### Vertical Layout (Default)
```php
$form->verticalLayout();
```

#### Horizontal Layout
```php
$form->horizontalLayout()
    ->labelWidth('150px')
    ->fieldWidth('300px');
```

#### Inline Layout
```php
$form->inlineLayout();
```

### 3. Validation

#### Validation Modes
```php
$form->validationMode('onSubmit');   // Validate on submit (default)
$form->validationMode('onChange');   // Validate as user types
$form->validationMode('onBlur');     // Validate when field loses focus
$form->validationMode('immediate');  // Validate immediately
```

#### Error Display
```php
$form->errorDisplay('inline');   // Show errors inline with fields (default)
$form->errorDisplay('summary');  // Show all errors in summary at top
$form->errorDisplay('toast');    // Show errors as toast notifications
$form->errorDisplay('none');     // Don't display errors
```

#### Validation Rules
```php
$form->addValidationRule('email', 'required', null, 'Email is required');
$form->addValidationRule('email', 'email', null, 'Must be valid email');
$form->addValidationRule('age', 'min', 18, 'Must be at least 18');
$form->addValidationRule('age', 'max', 100);
```

#### Error Messages
```php
$form->setErrorMessage('username', 'Username is already taken');
$form->errorSummaryTitle('Please fix the following errors:');
```

#### Error Behavior
```php
$form->focusOnError(true);   // Focus first field with error
$form->scrollToError(true);  // Scroll to first error
```

### 4. Buttons

#### Submit Button
```php
$form->submitButton('Save');

$form->submitButton('Create Account', [
    'style' => 'success',
    'size' => 'large',
    'full_width' => true,
]);
```

#### Reset Button
```php
$form->resetButton('Clear Form');
```

#### Cancel Button
```php
$form->cancelButton('Cancel', [
    'onclick' => 'history.back()',
]);
```

#### Button Configuration
```php
$form->submitStyle('primary')        // default, primary, success, danger, warning, info
    ->submitSize('large')            // small, medium, large
    ->submitPosition('center')       // left, center, right, space-between
    ->fullWidthButtons(true);
```

### 5. Field Organization

#### Sections
```php
$form->section('Personal Information', function ($section) {
    $section->add(
        UIBuilder::input('first_name')->label('First Name')->required()
    );
    $section->add(
        UIBuilder::input('last_name')->label('Last Name')->required()
    );
});

$form->section('Contact Information', function ($section) {
    $section->add(
        UIBuilder::input('email')->type('email')->required()
    );
});
```

#### Fieldsets
```php
$form->fieldset('Account Details', function ($fieldset) {
    $fieldset->add(
        UIBuilder::input('username')->label('Username')
    );
    $fieldset->add(
        UIBuilder::input('password')->type('password')->label('Password')
    );
});
```

### 6. Adding Fields

#### Input Fields
```php
$form->input('email', 'Email Address')
    ->type('email')
    ->placeholder('your@email.com')
    ->required(true);
```

#### Select Fields
```php
$form->select('country', 'Country')
    ->options([
        ['value' => 'us', 'label' => 'United States'],
        ['value' => 'uk', 'label' => 'United Kingdom'],
    ])
    ->searchable(true)
    ->required(true);
```

#### Checkbox Fields
```php
$form->checkbox('terms', 'I accept the Terms and Conditions')
    ->required(true);

$form->checkbox('interests')
    ->label('Interests')
    ->options([
        ['value' => 'sports', 'label' => 'Sports'],
        ['value' => 'music', 'label' => 'Music'],
        ['value' => 'tech', 'label' => 'Technology'],
    ]);
```

### 7. Submission Behavior

#### Loading State
```php
$form->showProgress(true);
$form->loadingMessage('Saving your data...');
```

#### Success Handling
```php
$form->successMessage('Data saved successfully!');
$form->redirectOnSuccess('/dashboard');
$form->resetOnSuccess(true);  // Clear form after success
```

#### Error Handling
```php
$form->errorMessage('Failed to save data');
```

#### Confirmation
```php
$form->confirmBeforeSubmit('Are you sure you want to submit this form?');
```

### 8. Auto-Save

```php
$form->autoSave(true, 5000);  // Auto-save every 5 seconds
```

### 9. Security

#### CSRF Protection
```php
$form->csrfToken('abc123token')
    ->csrfField('_token');
```

#### Honeypot (Spam Protection)
```php
$form->honeypot(true, '_gotcha');
```

### 10. File Uploads

```php
$form->multipart(true)                    // Enable multipart/form-data
    ->maxFileSize(10485760)               // 10MB max
    ->allowedFileTypes(['jpg', 'png', 'pdf']);
```

### 11. Styling

#### Visual Style
```php
$form->bordered(true)
    ->shadow(true)
    ->rounded(true)
    ->padding('large')
    ->backgroundColor('#f5f5f5');
```

#### Spacing
```php
$form->fieldSpacing('medium')      // xs, small, medium, large, xl
    ->sectionSpacing('large')
    ->condensed(false);
```

#### Custom Styling
```php
$form->customClass('my-custom-form')
    ->customStyle('border: 2px solid red;')
    ->dataAttributes(['test' => 'value']);
```

### 12. Field Indicators

```php
$form->showRequiredIndicator(true);   // Show * for required fields
$form->showOptionalIndicator(true);   // Show (optional) for optional fields
```

### 13. Event Handlers

```php
$form->onSubmit('handleSubmit')
    ->onReset('handleReset')
    ->onChange('handleChange')
    ->beforeSubmit('beforeSubmit')
    ->afterSubmit('afterSubmit')
    ->onValidate('validateForm');
```

### 14. Form States

```php
$form->disabled(true);   // Disable entire form
$form->readonly(true);   // Make entire form read-only
```

### 15. Accessibility

```php
$form->ariaLabel('User registration form')
    ->ariaDescribedBy('form-help-text');
```

### 16. Autocomplete

```php
$form->autocomplete(true);   // Enable browser autocomplete
$form->autocomplete(false);  // Disable browser autocomplete
```

## Complete Examples

### User Registration Form

```php
$form = UIBuilder::form('user-registration')
    ->action('/api/register')
    ->method('POST')
    ->ajax(true)
    ->verticalLayout()
    ->validationMode('onChange')
    ->errorDisplay('inline')
    ->preventMultipleSubmit(true)
    ->showProgress(true)
    ->loadingMessage('Creating your account...')
    ->successMessage('Account created successfully!')
    ->redirectOnSuccess('/dashboard')
    ->bordered(true)
    ->rounded(true)
    ->padding('large');

$form->section('Account Information', function ($section) {
    $section->add(
        UIBuilder::input('username')
            ->label('Username')
            ->placeholder('Choose a username')
            ->required(true)
            ->minlength(3)
            ->maxlength(20)
    );
    
    $section->add(
        UIBuilder::input('email')
            ->label('Email Address')
            ->type('email')
            ->placeholder('your@email.com')
            ->required(true)
    );
    
    $section->add(
        UIBuilder::input('password')
            ->label('Password')
            ->type('password')
            ->required(true)
            ->minlength(8)
    );
});

$form->section('Profile', function ($section) {
    $section->add(
        UIBuilder::input('first_name')
            ->label('First Name')
            ->required(true)
    );
    
    $section->add(
        UIBuilder::input('last_name')
            ->label('Last Name')
            ->required(true)
    );
    
    $section->add(
        UIBuilder::select('country')
            ->label('Country')
            ->options([
                ['value' => 'us', 'label' => 'United States'],
                ['value' => 'uk', 'label' => 'United Kingdom'],
                ['value' => 'ca', 'label' => 'Canada'],
            ])
            ->searchable(true)
            ->required(true)
    );
});

$form->checkbox('terms', 'I accept the Terms and Conditions')
    ->required(true);

$form->checkbox('newsletter', 'Send me updates and news');

$form->submitButton('Create Account', ['style' => 'success', 'size' => 'large'])
    ->cancelButton('Cancel', ['onclick' => 'history.back()']);
```

### Settings Form

```php
$form = UIBuilder::form('user-settings')
    ->action('/api/settings')
    ->method('PUT')
    ->ajax(true)
    ->horizontalLayout()
    ->labelWidth('180px')
    ->autoSave(true, 3000)
    ->bordered(true);

$form->fieldset('Display Settings', function ($fieldset) {
    $fieldset->add(
        UIBuilder::select('theme')
            ->label('Theme')
            ->options([
                ['value' => 'light', 'label' => 'Light'],
                ['value' => 'dark', 'label' => 'Dark'],
                ['value' => 'auto', 'label' => 'Auto'],
            ])
    );
    
    $fieldset->add(
        UIBuilder::checkbox('animations')
            ->label('Enable Animations')
            ->asSwitch()
            ->checked(true)
    );
});

$form->fieldset('Privacy', function ($fieldset) {
    $fieldset->add(
        UIBuilder::checkbox('show_online')
            ->label('Show Online Status')
            ->asSwitch()
    );
    
    $fieldset->add(
        UIBuilder::checkbox('allow_messages')
            ->label('Allow Direct Messages')
            ->asSwitch()
            ->checked(true)
    );
});

$form->submitButton('Save Settings')
    ->resetButton('Reset to Defaults');
```

### File Upload Form

```php
$form = UIBuilder::form('file-upload')
    ->action('/api/upload')
    ->method('POST')
    ->ajax(true)
    ->multipart(true)
    ->maxFileSize(10485760)  // 10MB
    ->allowedFileTypes(['jpg', 'png', 'pdf'])
    ->showProgress(true)
    ->loadingMessage('Uploading files...');

$form->input('title', 'Title')
    ->required(true);

$form->input('file', 'Choose File')
    ->type('file')
    ->accept('image/jpeg,image/png,application/pdf')
    ->required(true);

$form->submitButton('Upload');
```

## JSON Output Structure

The form builder generates a flat JSON structure:

```json
{
  "12345": {
    "type": "form",
    "action": "/api/users",
    "method": "POST",
    "ajax": true,
    "form_layout": "vertical",
    "validation_mode": "onSubmit",
    "buttons": [
      {"type": "submit", "label": "Save"},
      {"type": "reset", "label": "Clear"}
    ],
    "sections": [
      {"title": "Personal Info", "id": 12346}
    ],
    "slot": null
  },
  "12346": {
    "type": "section",
    "title": "Personal Info",
    "slot": 12345
  },
  "12347": {
    "type": "input",
    "label": "Username",
    "required": true,
    "slot": 12346
  }
}
```

## Validation Rules

The form supports adding validation rules that can be used by client-side or server-side validation:

```php
$form->addValidationRule('email', 'required');
$form->addValidationRule('email', 'email');
$form->addValidationRule('age', 'min', 18);
$form->addValidationRule('age', 'max', 100);
$form->addValidationRule('username', 'regex', '/^[a-zA-Z0-9]+$/');
```

These rules are included in the JSON output:

```json
{
  "validation_rules": {
    "email": [
      {"rule": "required", "value": null, "message": null},
      {"rule": "email", "value": null, "message": null}
    ],
    "age": [
      {"rule": "min", "value": 18, "message": "Must be at least 18"},
      {"rule": "max", "value": 100, "message": null}
    ]
  }
}
```

## Inheritance from UIContainer

Since `FormBuilder` extends `UIContainer`, you can use all container methods:

```php
$form = UIBuilder::form('my-form');

// Add any UI element
$button = UIBuilder::button('submit')->label('Submit');
$form->add($button);

// Find elements
$found = $form->find((string)$button->getId());

// Remove elements
$form->remove((string)$button->getId());

// Get all children
$children = $form->getChildren();

// Clear all children
$form->clear();
```

## API Reference

All methods return `$this` for method chaining (fluent API).

### Form Configuration Methods
- `action(?string $action)` - Set form action URL
- `method(string $method)` - Set HTTP method (GET, POST, PUT, PATCH, DELETE)
- `ajax(bool $ajax = true)` - Enable/disable AJAX submission
- `validate(bool $validate = true)` - Enable/disable client validation
- `autocomplete(bool $autocomplete = true)` - Enable/disable autocomplete
- `encoding(string $encoding)` - Set encoding type
- `multipart(bool $multipart = true)` - Enable multipart encoding (for files)

### Layout Methods
- `horizontalLayout()` - Set horizontal layout
- `verticalLayout()` - Set vertical layout
- `inlineLayout()` - Set inline layout
- `labelWidth(string $width)` - Set label width for horizontal forms
- `fieldWidth(string $width)` - Set field width for horizontal forms

### Validation Methods
- `validationMode(string $mode)` - Set validation mode
- `errorDisplay(string $display)` - Set error display mode
- `errorSummaryTitle(string $title)` - Set error summary title
- `focusOnError(bool $focus = true)` - Focus first field with error
- `scrollToError(bool $scroll = true)` - Scroll to first error
- `addValidationRule(string $field, string $rule, $value, ?string $message)` - Add validation rule
- `setErrorMessage(string $field, string $message)` - Set custom error message

### Button Methods
- `submitButton(string $label, array $config = [])` - Add submit button
- `resetButton(string $label, array $config = [])` - Add reset button
- `cancelButton(string $label, array $config = [])` - Add cancel button
- `submitStyle(string $style)` - Set submit button style
- `submitSize(string $size)` - Set submit button size
- `submitPosition(string $position)` - Set submit button position
- `fullWidthButtons(bool $fullWidth = true)` - Make buttons full width

### Organization Methods
- `section(string $title, callable $callback)` - Add form section
- `fieldset(string $legend, callable $callback)` - Add fieldset group

### Field Methods
- `input(string $name, ?string $label = null)` - Add input field
- `select(string $name, ?string $label = null)` - Add select field
- `checkbox(string $name, ?string $label = null)` - Add checkbox field

### Submission Methods
- `preventMultipleSubmit(bool $prevent = true)` - Prevent multiple submissions
- `showProgress(bool $show = true)` - Show progress indicator
- `loadingMessage(string $message)` - Set loading message
- `successMessage(string $message)` - Set success message
- `errorMessage(string $message)` - Set error message
- `redirectOnSuccess(string $url)` - Redirect after success
- `resetOnSuccess(bool $reset = true)` - Reset form after success
- `confirmBeforeSubmit(string $message)` - Show confirmation dialog

### Auto-save Methods
- `autoSave(bool $autoSave = true, int $delay = 3000)` - Enable auto-save

### Security Methods
- `csrfToken(string $token)` - Set CSRF token
- `csrfField(string $fieldName = '_token')` - Set CSRF field name
- `honeypot(bool $honeypot = true, string $fieldName = '_gotcha')` - Add honeypot

### File Upload Methods
- `maxFileSize(int $bytes)` - Set max file size
- `allowedFileTypes(array $types)` - Set allowed file types

### Styling Methods
- `bordered(bool $bordered = true)` - Add border
- `shadow(bool $shadow = true)` - Add shadow
- `rounded(bool $rounded = true)` - Add rounded corners
- `padding(string $padding)` - Set padding
- `backgroundColor(string $color)` - Set background color
- `fieldSpacing(string $spacing)` - Set field spacing
- `sectionSpacing(string $spacing)` - Set section spacing
- `condensed(bool $condensed = true)` - Use condensed spacing
- `customClass(string $class)` - Add custom CSS class
- `customStyle(string $style)` - Add custom inline style
- `dataAttributes(array $attributes)` - Add custom data attributes

### Accessibility Methods
- `ariaLabel(string $label)` - Set ARIA label
- `ariaDescribedBy(string $id)` - Set ARIA described-by

### State Methods
- `disabled(bool $disabled = true)` - Disable entire form
- `readonly(bool $readonly = true)` - Make entire form read-only

### Event Methods
- `onSubmit(string $handler)` - Set submit handler
- `onReset(string $handler)` - Set reset handler
- `onValidate(string $handler)` - Set validation handler
- `onChange(string $handler)` - Set change handler
- `beforeSubmit(string $handler)` - Set before-submit handler
- `afterSubmit(string $handler)` - Set after-submit handler

### Indicator Methods
- `showRequiredIndicator(bool $show = true)` - Show required indicator
- `showOptionalIndicator(bool $show = true)` - Show optional indicator

## Testing

FormBuilder has comprehensive test coverage:
- 30 unit tests (114 assertions)
- 6 integration tests with BBA/CNT game apps (28 assertions)
- Total: 36 tests, 142 assertions

Run tests with:
```bash
./vendor/bin/pest tests/Unit/Services/UI/FormBuilderTest.php
./vendor/bin/pest tests/Feature/Services/UI/FormBuilderIntegrationTest.php
```
