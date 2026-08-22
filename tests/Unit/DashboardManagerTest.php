<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\DashboardWidgetAuthorizationInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRepositoryInterface;
use Syscage\Plugin\Events\WidgetConfigured;
use Syscage\Plugin\Events\WidgetDisabled;
use Syscage\Plugin\Events\WidgetEnabled;
use Syscage\Plugin\Models\DashboardWidgetRecord;
use Syscage\Plugin\Tests\Support\FakeDashboardWidget;
use Syscage\Plugin\Widgets\DashboardManager;
use Syscage\Plugin\Widgets\DashboardWidgetManager;
use Syscage\Plugin\Widgets\DashboardWidgetRegistry;

final class DashboardManagerTest extends TestCase
{
    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('dashboard-cache-', true) . '.php';
    }

    protected function tearDown(): void
    {
        @unlink($this->cachePath);

        parent::tearDown();
    }

    private function makeUser(int $id = 1): Authenticatable
    {
        $user = $this->createStub(Authenticatable::class);
        $user->method('getAuthIdentifier')->willReturn($id);

        return $user;
    }

    private function makeWidgetManager(): DashboardWidgetManager
    {
        $registry = new DashboardWidgetRegistry();
        $registry->register(new FakeDashboardWidget(idValue: 'first.widget', defaultWidthValue: 4));
        $registry->register(new FakeDashboardWidget(idValue: 'second.widget', defaultWidthValue: 6, minWidthValue: 2, maxWidthValue: 8));

        $authorization = $this->createStub(DashboardWidgetAuthorizationInterface::class);
        $authorization->method('authorize')->willReturn(true);

        return new DashboardWidgetManager($registry, $authorization, new Dispatcher());
    }

    public function test_default_layout_orders_widgets_with_their_default_width(): void
    {
        $manager = new DashboardManager(
            $this->makeWidgetManager(),
            $this->createStub(DashboardWidgetRepositoryInterface::class),
            new Filesystem(),
            new Dispatcher(),
            $this->cachePath,
        );

        $layout = $manager->defaultLayout();

        $this->assertSame(['first.widget', 'second.widget'], array_map(
            static fn (array $entry): string => $entry['widget']->id(),
            $layout,
        ));
        $this->assertSame(4, $layout[0]['width']);
    }

    public function test_layout_for_applies_stored_position_and_width_and_excludes_disabled_widgets(): void
    {
        $repository = $this->createStub(DashboardWidgetRepositoryInterface::class);
        $repository->method('forUser')->willReturn(new Collection([
            new DashboardWidgetRecord(['widget_id' => 'first.widget', 'position' => 5, 'width' => 8, 'is_enabled' => true]),
            new DashboardWidgetRecord(['widget_id' => 'second.widget', 'is_enabled' => false]),
        ]));

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), new Dispatcher(), $this->cachePath);

        $layout = $manager->layoutFor($this->makeUser());

        $this->assertCount(1, $layout);
        $this->assertSame('first.widget', $layout[0]['widget']->id());
        $this->assertSame(5, $layout[0]['position']);
        $this->assertSame(8, $layout[0]['width']);
    }

    public function test_add_widget_upserts_at_the_next_position_with_the_default_width(): void
    {
        $repository = $this->createMock(DashboardWidgetRepositoryInterface::class);
        $repository->method('maxPositionForUser')->willReturn(2);
        $repository->expects($this->once())->method('upsert')->with(1, 'first.widget', [
            'is_enabled' => true,
            'position' => 3,
            'width' => 4,
        ])->willReturn(new DashboardWidgetRecord());

        $events = new Dispatcher();
        $dispatched = null;
        $events->listen(WidgetEnabled::class, function (WidgetEnabled $event) use (&$dispatched): void {
            $dispatched = $event;
        });

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), $events, $this->cachePath);
        $manager->addWidget($this->makeUser(), 'first.widget');

        $this->assertInstanceOf(WidgetEnabled::class, $dispatched);
    }

    public function test_remove_widget_disables_the_stored_row_and_dispatches_an_event(): void
    {
        $repository = $this->createMock(DashboardWidgetRepositoryInterface::class);
        $repository->expects($this->once())->method('upsert')->with(1, 'first.widget', ['is_enabled' => false])
            ->willReturn(new DashboardWidgetRecord());

        $events = new Dispatcher();
        $dispatched = null;
        $events->listen(WidgetDisabled::class, function (WidgetDisabled $event) use (&$dispatched): void {
            $dispatched = $event;
        });

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), $events, $this->cachePath);
        $manager->removeWidget($this->makeUser(), 'first.widget');

        $this->assertInstanceOf(WidgetDisabled::class, $dispatched);
    }

    public function test_resize_widget_clamps_to_the_widgets_min_and_max_width(): void
    {
        $repository = $this->createMock(DashboardWidgetRepositoryInterface::class);
        $repository->expects($this->once())->method('upsert')->with(1, 'second.widget', [
            'width' => 8,
            'height' => null,
        ])->willReturn(new DashboardWidgetRecord());

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), new Dispatcher(), $this->cachePath);
        $manager->resizeWidget($this->makeUser(), 'second.widget', 99);
    }

    public function test_configure_widget_persists_settings_and_dispatches_an_event(): void
    {
        $repository = $this->createMock(DashboardWidgetRepositoryInterface::class);
        $repository->expects($this->once())->method('upsert')->with(1, 'first.widget', ['settings' => ['period' => 'week']])
            ->willReturn(new DashboardWidgetRecord());

        $events = new Dispatcher();
        $dispatched = null;
        $events->listen(WidgetConfigured::class, function (WidgetConfigured $event) use (&$dispatched): void {
            $dispatched = $event;
        });

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), $events, $this->cachePath);
        $manager->configureWidget($this->makeUser(), 'first.widget', ['period' => 'week']);

        $this->assertSame(['period' => 'week'], $dispatched->settings);
    }

    public function test_reset_layout_deletes_every_stored_row_for_the_user(): void
    {
        $repository = $this->createMock(DashboardWidgetRepositoryInterface::class);
        $repository->expects($this->once())->method('deleteForUser')->with(1);

        $manager = new DashboardManager($this->makeWidgetManager(), $repository, new Filesystem(), new Dispatcher(), $this->cachePath);
        $manager->resetLayout($this->makeUser());
    }

    public function test_cache_default_layout_writes_and_forget_removes_the_cache_file(): void
    {
        $manager = new DashboardManager(
            $this->makeWidgetManager(),
            $this->createStub(DashboardWidgetRepositoryInterface::class),
            new Filesystem(),
            new Dispatcher(),
            $this->cachePath,
        );

        $manager->cacheDefaultLayout();
        $this->assertFileExists($this->cachePath);

        $manager->forgetDefaultLayoutCache();
        $this->assertFileDoesNotExist($this->cachePath);
    }
}
