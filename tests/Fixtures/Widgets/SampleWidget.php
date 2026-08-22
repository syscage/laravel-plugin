<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Fixtures\Widgets;

use Syscage\Plugin\Widgets\DashboardWidget;

final class SampleWidget extends DashboardWidget
{
    public function id(): string
    {
        return 'sample.widget';
    }

    public function title(): string
    {
        return 'Sample Widget';
    }

    public function component(): string
    {
        return 'Sample/Widgets/Sample';
    }
}
