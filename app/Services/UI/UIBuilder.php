<?php

namespace App\Services\UI;

use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ContainerBuilder;
use App\Services\UI\Components\TableBuilder;

/**
 * Factory class for creating UI components
 * 
 * Provides static methods to create various UI component builders.
 * These builders use a fluent API for configuring components.
 */
class UIBuilder
{
    /**
     * Create a new button component
     * 
     * @param string $id The unique identifier for the button
     * @return ButtonBuilder
     */
    public static function button(string $id): ButtonBuilder
    {
        return new ButtonBuilder($id);
    }

    /**
     * Create a new label component
     * 
     * @param string $id The unique identifier for the label
     * @return LabelBuilder
     */
    public static function label(string $id): LabelBuilder
    {
        return new LabelBuilder($id);
    }

    /**
     * Create a new container component
     * 
     * @param string $id The unique identifier for the container
     * @return ContainerBuilder
     */
    public static function container(string $id): ContainerBuilder
    {
        return new ContainerBuilder($id);
    }

    /**
     * Create a new table component
     * 
     * @param string $id The unique identifier for the table
     * @return TableBuilder
     */
    public static function table(string $id): TableBuilder
    {
        return new TableBuilder($id);
    }
}