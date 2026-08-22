<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;

/**
 * Dispatched after a user has saved a widget's configuration settings.
 */
final class WidgetConfigured extends DashboardWidgetEvent
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        DashboardWidgetInterface $widget,
        public readonly int|string $userId,
        public readonly array $settings,
    ) {
        parent::__construct($widget);
    }
}
