<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\DashboardWidgetAuthorizationInterface;
use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Events\WidgetRegistered;
use Syscage\Plugin\Events\WidgetRemoved;
use Syscage\Plugin\Exceptions\DashboardWidgetNotFoundException;
use Syscage\Plugin\Tests\Support\FakeDashboardWidget;
use Syscage\Plugin\Widgets\DashboardWidgetManager;
use Syscage\Plugin\Widgets\DashboardWidgetRegistry;

final class DashboardWidgetManagerTest extends TestCase
{
    private function makeAuthorization(bool $allow): DashboardWidgetAuthorizationInterface
    {
        $authorization = $this->createStub(DashboardWidgetAuthorizationInterface::class);
        $authorization->method('authorize')->willReturn($allow);

        return $authorization;
    }

    public function test_register_adds_the_widget_and_dispatches_an_event(): void
    {
        $registry = new DashboardWidgetRegistry();
        $events = new Dispatcher();
        $manager = new DashboardWidgetManager($registry, $this->makeAuthorization(true), $events);
        $widget = new FakeDashboardWidget(idValue: 'demo.widget');

        $dispatched = null;
        $events->listen(WidgetRegistered::class, function (WidgetRegistered $event) use (&$dispatched): void {
            $dispatched = $event;
        });

        $manager->register($widget);

        $this->assertTrue($manager->has('demo.widget'));
        $this->assertInstanceOf(WidgetRegistered::class, $dispatched);
        $this->assertSame($widget, $dispatched->widget);
    }

    public function test_forget_removes_the_widget_and_dispatches_an_event(): void
    {
        $registry = new DashboardWidgetRegistry();
        $widget = new FakeDashboardWidget(idValue: 'demo.widget');
        $registry->register($widget);

        $events = new Dispatcher();
        $manager = new DashboardWidgetManager($registry, $this->makeAuthorization(true), $events);

        $dispatched = null;
        $events->listen(WidgetRemoved::class, function (WidgetRemoved $event) use (&$dispatched): void {
            $dispatched = $event;
        });

        $manager->forget('demo.widget');

        $this->assertFalse($manager->has('demo.widget'));
        $this->assertInstanceOf(WidgetRemoved::class, $dispatched);
    }

    public function test_forget_throws_when_the_id_is_unknown(): void
    {
        $manager = new DashboardWidgetManager(new DashboardWidgetRegistry(), $this->makeAuthorization(true), new Dispatcher());

        $this->expectException(DashboardWidgetNotFoundException::class);

        $manager->forget('missing.widget');
    }

    public function test_available_for_filters_widgets_through_authorization(): void
    {
        $registry = new DashboardWidgetRegistry();
        $registry->register(new FakeDashboardWidget(idValue: 'visible.widget'));
        $registry->register(new FakeDashboardWidget(idValue: 'hidden.widget'));

        $authorization = $this->createStub(DashboardWidgetAuthorizationInterface::class);
        $authorization->method('authorize')->willReturnCallback(
            static fn (DashboardWidgetInterface $widget): bool => $widget->id() === 'visible.widget',
        );

        $manager = new DashboardWidgetManager($registry, $authorization, new Dispatcher());
        $user = $this->createStub(Authenticatable::class);

        $this->assertSame(['visible.widget'], array_keys($manager->availableFor($user)));
    }
}
