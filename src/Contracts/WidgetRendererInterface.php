<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Resolves a widget's frontend component reference against the host
 * application's detected frontend stack, keeping widgets from being
 * tightly coupled to a specific frontend framework.
 */
interface WidgetRendererInterface
{
    /**
     * Resolve the identifier used to render the given widget: a Blade view
     * name when the host uses Blade, or the widget's own component
     * reference unchanged for React/Vue/Inertia frontends.
     */
    public function resolve(DashboardWidgetInterface $widget): string;
}
