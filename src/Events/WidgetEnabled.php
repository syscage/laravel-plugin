<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;

/**
 * Dispatched after a user has enabled a widget on their dashboard.
 */
final class WidgetEnabled extends DashboardWidgetEvent
{
    public function __construct(
        DashboardWidgetInterface $widget,
        public readonly int|string $userId,
    ) {
        parent::__construct($widget);
    }
}
