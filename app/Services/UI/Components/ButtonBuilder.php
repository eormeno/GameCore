<?php

namespace App\Services\UI\Components;

/**
 * Builder for Button UI components
 * 
 * Buttons are interactive elements that trigger actions when clicked.
 * They can have labels, icons, tooltips, and various styles.
 */
class ButtonBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'style' => 'default',
            'label' => '',
            'action' => null,
            'parameters' => [],
            'icon' => null,
            'tooltip' => null,
        ];
    }

    /**
     * Set the button label text
     * 
     * @param string $label The label text
     * @return self For method chaining
     */
    public function label(string $label): self
    {
        return $this->setConfig('label', $label);
    }

    /**
     * Set the action to trigger when button is clicked
     * 
     * @param string $action The action name
     * @param array $parameters Optional parameters for the action
     * @return self For method chaining
     */
    public function action(string $action, array $parameters = []): self
    {
        $this->setConfig('action', $action);
        $this->setConfig('parameters', $parameters);
        return $this;
    }

    /**
     * Set the button icon
     * 
     * @param string $icon The icon name
     * @return self For method chaining
     */
    public function icon(string $icon): self
    {
        return $this->setConfig('icon', $icon);
    }

    /**
     * Set the button style
     * 
     * @param string $style The style name (primary, success, danger, warning, default)
     * @return self For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
    }

    /**
     * Set whether the button is enabled
     * 
     * @param bool $enabled True if enabled, false if disabled
     * @return self For method chaining
     */
    public function enabled(bool $enabled = true): self
    {
        return $this->setConfig('enabled', $enabled);
    }

    /**
     * Set the button tooltip text
     * 
     * @param string $tooltip The tooltip text
     * @return self For method chaining
     */
    public function tooltip(string $tooltip): self
    {
        return $this->setConfig('tooltip', $tooltip);
    }

    /**
     * Legacy build method for backward compatibility
     * Returns array format instead of object
     * 
     * @return array
     * @deprecated Use toJson() instead
     */
    public function build(): array
    {
        return $this->toJson();
    }
}