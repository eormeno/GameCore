<?php

namespace App\Services\UI;

use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\ContainerBuilder;
use App\Services\UI\Components\TableBuilder;
use App\Services\UI\Components\TableRowBuilder;
use App\Services\UI\Components\InputBuilder;

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
     * @param string $name an optional semantic name for the button
     * @return ButtonBuilder
     */
    public static function button(?string $name = null): ButtonBuilder
    {
        return new ButtonBuilder($name);
    }

    /**
     * Create a new label component
     * 
     * @param string $name an optional semantic name for the label
     * @return LabelBuilder
     */
    public static function label(?string $name = null): LabelBuilder
    {
        return new LabelBuilder($name);
    }

    /**
     * Create a new container component
     * 
     * @param string $name The optional semantic name for the container
     * @return ContainerBuilder
     */
    public static function container(?string $name = null): ContainerBuilder
    {
        return new ContainerBuilder($name);
    }

    /**
     * Create a new table component
     * 
     * @param string $name The optional semantic name for the table
     * @return TableBuilder
     */
    public static function table(?string $name = null): TableBuilder
    {
        return new TableBuilder($name);
    }

    /**
     * Create a new table row component
     * 
     * @param TableBuilder $table The parent table this row belongs to
     * @param string|null $name The optional semantic name for the row
     * @return TableRowBuilder
     */
    public static function tableRow(TableBuilder $table, ?string $name = null): TableRowBuilder
    {
        return new TableRowBuilder($table, $name);
    }

    public static function input(?string $name = null): InputBuilder
    {
        return new InputBuilder($name);
    }
}