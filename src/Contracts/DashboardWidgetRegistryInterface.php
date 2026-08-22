<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;

/**
 * In-memory collection of the dashboard widgets known to the current
 * request.
 */
interface DashboardWidgetRegistryInterface
{
    /**
     * Register a widget, keyed by its id.
     */
    public function register(DashboardWidgetInterface $widget): void;

    /**
     * Remove a widget from the registry.
     */
    public function forget(string $id): void;

    /**
     * Determine whether a widget with the given id is registered.
     */
    public function has(string $id): bool;

    /**
     * Retrieve a registered widget by its id.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function find(string $id): DashboardWidgetInterface;

    /**
     * All registered widgets, keyed by id.
     *
     * @return array<string, DashboardWidgetInterface>
     */
    public function all(): array;

    /**
     * All registered widgets whose owning plugin is currently enabled, keyed
     * by id. Since widget discovery only ever registers widgets contributed
     * by enabled plugins, this is currently equivalent to {@see all()}; it
     * exists as its own method so the two concepts stay independently
     * overridable as the framework evolves.
     *
     * @return array<string, DashboardWidgetInterface>
     */
    public function enabled(): array;
}
