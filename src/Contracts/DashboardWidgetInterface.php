<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Syscage\Plugin\Widgets\DashboardWidgetType;

/**
 * Represents a single dashboard widget contributed by a plugin.
 *
 * Widgets do not control their own placement on the dashboard: the
 * dashboard controls position, width, and ordering, while a widget only
 * describes what it is, how it should be rendered, and who may see it.
 *
 * Plugins should extend {@see \Syscage\Plugin\Widgets\DashboardWidget}
 * rather than implementing this interface directly, unless they require
 * custom behavior for every metadata accessor.
 */
interface DashboardWidgetInterface
{
    /**
     * The widget's unique identifier, e.g. "myblog.messages-today".
     */
    public function id(): string;

    /**
     * The widget's human-readable title.
     */
    public function title(): string;

    /**
     * A short description of what the widget displays.
     */
    public function description(): ?string;

    /**
     * The frontend component reference resolved by the widget renderer,
     * e.g. "MyBlog/Widgets/MessagesToday".
     */
    public function component(): string;

    /**
     * The permission required to view this widget, or null when every user
     * that can reach the dashboard may see it.
     */
    public function permission(): ?string;

    /**
     * The widget's presentation type.
     */
    public function type(): DashboardWidgetType;

    /**
     * The icon identifier displayed alongside the widget's title.
     */
    public function icon(): ?string;

    /**
     * The roles allowed to see this widget, in addition to its permission.
     * An empty array means no role restriction applies.
     *
     * @return array<int, string>
     */
    public function roles(): array;

    /**
     * The widget's default width, in dashboard grid columns.
     */
    public function defaultWidth(): int;

    /**
     * The minimum width a user may resize this widget to.
     */
    public function minWidth(): int;

    /**
     * The maximum width a user may resize this widget to.
     */
    public function maxWidth(): int;

    /**
     * The interval, in seconds, after which the dashboard should
     * automatically refresh this widget's data. Null disables auto-refresh.
     */
    public function refreshInterval(): ?int;

    /**
     * The widget's configurable settings, used to generate a widget
     * configuration interface.
     *
     * @return array<int, WidgetConfigurationInterface>
     */
    public function settings(): array;

    /**
     * The widget's backend data, provided independently from its frontend
     * component so the dashboard can lazy-load it.
     *
     * @return array<string, mixed>
     */
    public function data(): array;
}
