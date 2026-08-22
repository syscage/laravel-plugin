<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * A single configurable setting exposed by a dashboard widget (e.g. a
 * "period" select box), used by the host dashboard to generate a widget
 * configuration interface.
 */
interface WidgetConfigurationInterface
{
    /**
     * The setting's unique key within the widget, e.g. "period".
     */
    public function key(): string;

    /**
     * The input type, e.g. "select", "text", "boolean", "number".
     */
    public function type(): string;

    /**
     * The setting's default value.
     */
    public function default(): mixed;

    /**
     * The list of selectable values, when applicable (e.g. for "select").
     *
     * @return array<int, mixed>
     */
    public function options(): array;

    /**
     * The setting definition in array form, as consumed by the dashboard's
     * generated widget configuration interface.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
