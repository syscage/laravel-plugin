<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Fixtures\WidgetPlugin\Widgets;

use Syscage\Plugin\Widgets\DashboardWidget;

final class DemoWidget extends DashboardWidget
{
    public function id(): string
    {
        return 'widget-plugin.demo';
    }

    public function title(): string
    {
        return 'Demo Widget';
    }

    public function component(): string
    {
        return 'WidgetPlugin/Widgets/Demo';
    }
}
