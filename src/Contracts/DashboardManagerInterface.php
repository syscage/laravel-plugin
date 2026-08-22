<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;

/**
 * Builds and mutates a user's personal dashboard layout by merging the
 * widgets a user is authorized to see with their stored layout
 * customizations. Widgets never control their own placement: the dashboard
 * controls position, width, and ordering, while the user controls their own
 * personal preferences within the bounds each widget declares.
 */
interface DashboardManagerInterface
{
    /**
     * The default dashboard layout, built from every enabled widget in its
     * registration order and each widget's own default width. Used as the
     * starting point for a user that has not customized their dashboard.
     *
     * @return array<int, array{widget: DashboardWidgetInterface, position: int, width: int}>
     */
    public function defaultLayout(): array;

    /**
     * The given user's personal dashboard layout: every widget they are
     * authorized to see, positioned and sized according to their stored
     * preferences (falling back to registration order and default width for
     * widgets they have not customized), ordered by position.
     *
     * @return array<int, array{
     *     widget: DashboardWidgetInterface,
     *     position: int,
     *     width: int,
     *     height: int|null,
     *     settings: array<string, mixed>,
     * }>
     */
    public function layoutFor(Authenticatable $user): array;

    /**
     * Add a widget to a user's dashboard.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function addWidget(Authenticatable $user, string $widgetId): void;

    /**
     * Remove (hide) a widget from a user's dashboard.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function removeWidget(Authenticatable $user, string $widgetId): void;

    /**
     * Move a widget to a new position on a user's dashboard.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function moveWidget(Authenticatable $user, string $widgetId, int $position): void;

    /**
     * Resize a widget on a user's dashboard.
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function resizeWidget(Authenticatable $user, string $widgetId, int $width, ?int $height = null): void;

    /**
     * Save a widget's configuration settings for a user, dispatching
     * {@see \Syscage\Plugin\Events\WidgetConfigured}.
     *
     * @param array<string, mixed> $settings
     *
     * @throws DashboardWidgetNotFoundException
     */
    public function configureWidget(Authenticatable $user, string $widgetId, array $settings): void;

    /**
     * Discard every stored layout customization for a user, reverting their
     * dashboard to the default layout.
     */
    public function resetLayout(Authenticatable $user): void;

    /**
     * Build and persist the default layout cache, returning it.
     *
     * @return array<int, array{widget: DashboardWidgetInterface, position: int, width: int}>
     */
    public function cacheDefaultLayout(): array;

    /**
     * Remove the default layout cache, if one exists.
     */
    public function forgetDefaultLayoutCache(): void;
}
