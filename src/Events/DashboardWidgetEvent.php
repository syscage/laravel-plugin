<?php

declare(strict_types=1);

namespace Syscage\Plugin\Events;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;

/**
 * Base class for every dashboard widget lifecycle event.
 */
abstract class DashboardWidgetEvent
{
    public function __construct(
        public readonly DashboardWidgetInterface $widget,
    ) {
    }
}
