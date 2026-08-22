<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\DashboardWidgetClassNotFoundException;
use Syscage\Plugin\Tests\Fixtures\Widgets\NotAWidget;
use Syscage\Plugin\Tests\Fixtures\Widgets\SampleWidget;
use Syscage\Plugin\Tests\Support\FakePlugin;
use Syscage\Plugin\Widgets\DashboardWidgetCache;
use Syscage\Plugin\Widgets\DashboardWidgetDiscovery;
use Syscage\Plugin\Widgets\DashboardWidgetRegistry;

final class DashboardWidgetDiscoveryTest extends TestCase
{
    private string $cachePath;

    private DashboardWidgetDiscovery $discovery;

    private DashboardWidgetRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('widget-discovery-', true) . '.php';
        $this->registry = new DashboardWidgetRegistry();

        $this->discovery = new DashboardWidgetDiscovery(
            new Container(),
            new DashboardWidgetCache(new Filesystem(), $this->cachePath),
            $this->registry,
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->cachePath);

        parent::tearDown();
    }

    private function pluginWithWidget(): FakePlugin
    {
        return new FakePlugin(alias: 'demo-plugin', widgetsValue: [SampleWidget::class]);
    }

    public function test_it_discovers_widgets_declared_by_a_plugin(): void
    {
        $widgets = $this->discovery->discover(['demo-plugin' => $this->pluginWithWidget()], fresh: true);

        $this->assertArrayHasKey('sample.widget', $widgets);
        $this->assertInstanceOf(SampleWidget::class, $widgets['sample.widget']);
    }

    public function test_it_populates_the_registry(): void
    {
        $this->discovery->discover(['demo-plugin' => $this->pluginWithWidget()], fresh: true);

        $this->assertTrue($this->registry->has('sample.widget'));
    }

    public function test_it_writes_and_then_reuses_the_cache(): void
    {
        $fromFilesystem = $this->discovery->discover(['demo-plugin' => $this->pluginWithWidget()], fresh: true);
        $fromCache = $this->discovery->discover([], fresh: false);

        $this->assertSame(
            $fromFilesystem['sample.widget']->id(),
            $fromCache['sample.widget']->id(),
        );
    }

    public function test_it_throws_when_the_widget_class_does_not_exist(): void
    {
        $plugin = new FakePlugin(alias: 'demo-plugin', widgetsValue: ['Nonexistent\\Widget']);

        $this->expectException(DashboardWidgetClassNotFoundException::class);

        $this->discovery->discover(['demo-plugin' => $plugin], fresh: true);
    }

    public function test_it_throws_when_the_widget_class_has_the_wrong_type(): void
    {
        $plugin = new FakePlugin(alias: 'demo-plugin', widgetsValue: [NotAWidget::class]);

        $this->expectException(DashboardWidgetClassNotFoundException::class);

        $this->discovery->discover(['demo-plugin' => $plugin], fresh: true);
    }
}
