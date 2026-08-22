<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;

/**
 * Base class for every plugin-provided dashboard widget.
 *
 * Supplies sensible defaults for every optional metadata accessor, leaving
 * {@see id()}, {@see title()}, and {@see component()} as the only members a
 * concrete widget must implement.
 */
abstract class DashboardWidget implements DashboardWidgetInterface
{
    abstract public function id(): string;

    abstract public function title(): string;

    abstract public function component(): string;

    public function description(): ?string
    {
        return null;
    }

    public function permission(): ?string
    {
        return null;
    }

    public function type(): DashboardWidgetType
    {
        return DashboardWidgetType::Custom;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function roles(): array
    {
        return [];
    }

    public function defaultWidth(): int
    {
        return 4;
    }

    public function minWidth(): int
    {
        return 2;
    }

    public function maxWidth(): int
    {
        return 12;
    }

    public function refreshInterval(): ?int
    {
        return null;
    }

    public function settings(): array
    {
        return [];
    }

    public function data(): array
    {
        return [];
    }
}
