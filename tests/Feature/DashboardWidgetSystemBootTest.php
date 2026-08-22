<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\DashboardManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetManagerInterface;
use Syscage\Plugin\Contracts\WidgetRendererInterface;
use Syscage\Plugin\Tests\TestCase;

/**
 * An end-to-end test proving the dashboard widget system's service provider
 * wiring works together the way a real host application would experience
 * it: booting the framework automatically discovers a plugin's widgets,
 * makes them available to an authorized user, and the console commands
 * manage their compiled caches.
 */
final class DashboardWidgetSystemBootTest extends TestCase
{
    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $this->cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('widget-full-boot-', true);

        $app['config']->set('plugin.plugins_path', realpath(__DIR__ . '/../Fixtures/widget-plugins'));
        $app['config']->set('plugin.cache.plugins', $this->cacheDirectory . '/plugins.php');
        $app['config']->set('plugin.cache.sidebar', $this->cacheDirectory . '/sidebar.php');
        $app['config']->set('plugin.cache.widgets', $this->cacheDirectory . '/widgets.php');
        $app['config']->set('plugin.cache.dashboard', $this->cacheDirectory . '/dashboard.php');
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    private function makeUser(int $id = 1): Authenticatable
    {
        $user = $this->createStub(Authenticatable::class);
        $user->method('getAuthIdentifier')->willReturn($id);

        return $user;
    }

    public function test_booting_the_framework_discovers_a_fixture_plugins_widget(): void
    {
        $widgets = $this->app->make(DashboardWidgetManagerInterface::class);

        $this->assertTrue($widgets->has('widget-plugin.demo'));
        $this->assertSame('Demo Widget', $widgets->find('widget-plugin.demo')->title());
    }

    public function test_an_authorized_user_can_see_the_discovered_widget_on_their_dashboard(): void
    {
        $dashboard = $this->app->make(DashboardManagerInterface::class);

        $layout = $dashboard->layoutFor($this->makeUser());

        $this->assertSame(
            ['widget-plugin.demo'],
            array_map(static fn (array $entry): string => $entry['widget']->id(), $layout),
        );
    }

    public function test_the_widget_renderer_resolves_the_components_frontend_reference(): void
    {
        $this->app['config']->set('plugin.frontend', 'react');

        $renderer = $this->app->make(WidgetRendererInterface::class);
        $widget = $this->app->make(DashboardWidgetManagerInterface::class)->find('widget-plugin.demo');

        $this->assertSame('WidgetPlugin/Widgets/Demo', $renderer->resolve($widget));
    }

    public function test_widget_and_dashboard_cache_commands_manage_their_compiled_caches(): void
    {
        $this->artisan('widget:cache')->assertSuccessful();
        $this->assertFileExists($this->cacheDirectory . '/widgets.php');

        $this->artisan('widget:clear')->assertSuccessful();
        $this->assertFileDoesNotExist($this->cacheDirectory . '/widgets.php');

        $this->artisan('dashboard:cache')->assertSuccessful();
        $this->assertFileExists($this->cacheDirectory . '/dashboard.php');

        $this->artisan('dashboard:clear')->assertSuccessful();
        $this->assertFileDoesNotExist($this->cacheDirectory . '/dashboard.php');
    }

    public function test_widget_list_command_lists_the_discovered_widget(): void
    {
        $this->artisan('widget:list')->assertSuccessful();
    }
}
