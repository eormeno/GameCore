<?php

namespace App\Services\UI\Components;

/**
 * Builder for Label UI components
 * 
 * Labels are text display elements that can have different styles
 * for various purposes (default, warning, error, success, etc.)
 */
class LabelBuilder extends UIComponent
{
    protected function getDefaultConfig(): array
    {
        return [
            'text' => '',
            'style' => 'default',
        ];
    }

    /**
     * Set the label text content
     * 
     * @param string $text The text to display
     * @return self For method chaining
     */
    public function text(string $text): self
    {
        return $this->setConfig('text', $text);
    }

    /**
     * Set the label style
     * 
     * @param string $style The style name (default, warning, error, success, info)
     * @return self For method chaining
     */
    public function style(string $style): self
    {
        return $this->setConfig('style', $style);
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