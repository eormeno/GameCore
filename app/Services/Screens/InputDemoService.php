<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;
use App\GameApps\Common\prefabs\Label;
use App\Services\UI\Traits\StoresUIState;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ButtonBuilder;

/**
 * Input Demo Service
 * 
 * Demonstrates input component functionality:
 * - Text input with placeholder
 * - Reading input value from frontend
 * - Updating input value from backend
 * - Label updates based on input
 */
class InputDemoService
{
    use StoresUIState;

    private UIContainer $container;
    private LabelBuilder $lbl_instruction;
    private LabelBuilder $lbl_result;
    private InputBuilder $input_text;
    private ButtonBuilder $btn_get_value;

    protected function buildBaseUI(): UIContainer
    {
        $this->container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Input Component Demo');

        $this->lbl_instruction = UIBuilder::label('lbl_instruction')
            ->text('📝 Type something in the input below and click "Get Value"')
            ->style('info');

        $this->container->add($this->lbl_instruction);

        $this->input_text = UIBuilder::input('input_text')
            ->placeholder('Enter your text here...')
            ->value('')
            ->required(false);
        $this->container->add($this->input_text);

        $this->btn_get_value = UIBuilder::button('btn_get_value')
            ->label('Get Value')
            ->action('get_value')
            ->style('primary');
        $this->container->add($this->btn_get_value);

        // Result label (initially empty)
        $this->lbl_result = UIBuilder::label('lbl_result')
            ->text('Result will appear here')
            ->style('default');

        $this->container->add($this->lbl_result);

        return $this->container;
    }

    /**
     * Get the demo screen with input components
     * 
     * Returns UI from cache or regenerates if not exists
     *
     * @return array
     */
    public function getInputDemoScreen(): array
    {
        return $this->getStoredUI();
    }

    /**
     * Handle "Get Value" button click
     * 
     * Reads the input value sent from frontend and displays it in the result label
     * 
     * @param array $params Event parameters (should include 'value' from input)
     * @return array Response with UI updates
     */
    public function onGetValue(array $params): array
    {
        // Get the input value from parameters
        $inputValue = $params['value'] ?? '';

        // Get UI container from cache
        $container = $this->getUIContainer();

        // Get JSON before changes
        $oldUI = $container->toJson();

        // Update result label with the input value
        $resultLabel = $container->findByName('lbl_result');
        if ($resultLabel && method_exists($resultLabel, 'text')) {
            /** @var \App\Services\UI\Components\LabelBuilder $resultLabel */
            if (empty($inputValue)) {
                $resultLabel->text('⚠️ Input is empty!')
                    ->style('warning');
            } else {
                $resultLabel->text("✅ You typed: \"$inputValue\"")
                    ->style('success');
            }
        }

        // Get JSON after changes
        $newUI = $container->toJson();

        // Save changes to cache
        $this->storeUI($container);

        // Calculate changes using UIDiffer
        $diff = \App\Services\UI\Support\UIDiffer::compare($oldUI, $newUI);

        // Asegurar formato indexado con _id incluido
        $result = [];
        foreach ($diff as $componentId => $changes) {
            $changes['_id'] = $componentId;
            $result[$componentId] = $changes; // Mantener índice por componentId
        }

        return $result;
    }
}
