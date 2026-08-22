<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;

/**
 * The primary API for dashboard widget operations, coordinating the widget
 * registry and widget authorization.
 */
interface DashboardWidgetManagerInterface
{
    /**
     * All registered widgets, keyed by id.
     *
     * @return array<string, DashboardWidgetInterface>
     */
    public function all(): array;

    /**
     * Retrieve a registered widget by its id.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function find(string $id): DashboardWidgetInterface;

    /**
     * Determine whether a widget with the given id is registered.
     */
    public function has(string $id): bool;

    /**
     * Register a widget at runtime, dispatching {@see \Syscage\Plugin\Events\WidgetRegistered}.
     */
    public function register(DashboardWidgetInterface $widget): void;

    /**
     * Remove a registered widget, dispatching {@see \Syscage\Plugin\Events\WidgetRemoved}.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function forget(string $id): void;

    /**
     * All widgets whose owning plugin is currently enabled, keyed by id.
     *
     * @return array<string, DashboardWidgetInterface>
     */
    public function enabled(): array;

    /**
     * The enabled widgets the given user is authorized to see, keyed by id.
     *
     * @return array<string, DashboardWidgetInterface>
     */
    public function availableFor(Authenticatable $user): array;
}
