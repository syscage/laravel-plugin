<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;
use Syscage\Plugin\Tests\Support\FakeDashboardWidget;
use Syscage\Plugin\Widgets\DashboardWidgetRegistry;

final class DashboardWidgetRegistryTest extends TestCase
{
    public function test_it_registers_and_retrieves_widgets_by_id(): void
    {
        $registry = new DashboardWidgetRegistry();
        $widget = new FakeDashboardWidget(idValue: 'demo.widget');

        $registry->register($widget);

        $this->assertTrue($registry->has('demo.widget'));
        $this->assertSame($widget, $registry->find('demo.widget'));
        $this->assertSame(['demo.widget' => $widget], $registry->all());
    }

    public function test_find_throws_when_the_id_is_unknown(): void
    {
        $registry = new DashboardWidgetRegistry();

        $this->expectException(DashboardWidgetNotFoundException::class);

        $registry->find('missing.widget');
    }

    public function test_forget_removes_a_widget(): void
    {
        $registry = new DashboardWidgetRegistry();
        $registry->register(new FakeDashboardWidget(idValue: 'demo.widget'));

        $registry->forget('demo.widget');

        $this->assertFalse($registry->has('demo.widget'));
    }

    public function test_enabled_returns_every_registered_widget(): void
    {
        $registry = new DashboardWidgetRegistry();
        $registry->register(new FakeDashboardWidget(idValue: 'first.widget'));
        $registry->register(new FakeDashboardWidget(idValue: 'second.widget'));

        $this->assertSame(['first.widget', 'second.widget'], array_keys($registry->enabled()));
    }
}
