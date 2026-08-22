<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRegistryInterface;
use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;

/**
 * Default in-memory implementation of the dashboard widget registry.
 */
final class DashboardWidgetRegistry implements DashboardWidgetRegistryInterface
{
    /**
     * @var array<string, DashboardWidgetInterface>
     */
    private array $widgets = [];

    public function register(DashboardWidgetInterface $widget): void
    {
        $this->widgets[$widget->id()] = $widget;
    }

    public function forget(string $id): void
    {
        unset($this->widgets[$id]);
    }

    public function has(string $id): bool
    {
        return isset($this->widgets[$id]);
    }

    public function find(string $id): DashboardWidgetInterface
    {
        return $this->widgets[$id] ?? throw DashboardWidgetNotFoundException::forId($id);
    }

    public function all(): array
    {
        return $this->widgets;
    }

    public function enabled(): array
    {
        return $this->widgets;
    }
}
