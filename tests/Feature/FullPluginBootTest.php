<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\Contracts\PluginManagerInterface;
use Syscage\Plugin\Tests\TestCase;

/**
 * An end-to-end test proving the entire chain — service provider wiring,
 * discovery, autoloading, dependency resolution, loading, and every
 * resource manager — works together the way a real host application would
 * experience it: by simply booting the framework and finding plugin
 * routes, views, translations, and commands already available.
 */
final class FullPluginBootTest extends TestCase
{
    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $this->cacheDirectory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-full-boot-', true);

        $app['config']->set('plugin.plugins_path', realpath(__DIR__ . '/../Fixtures/plugins'));
        $app['config']->set('plugin.cache.plugins', $this->cacheDirectory . '/plugins.php');
        $app['config']->set('plugin.cache.sidebar', $this->cacheDirectory . '/sidebar.php');
    }

    protected function tearDown(): void
    {
        (new \Illuminate\Filesystem\Filesystem())->deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    public function test_booting_the_framework_discovers_and_loads_every_fixture_plugin(): void
    {
        $manager = $this->app->make(PluginManagerInterface::class);

        $this->assertTrue($manager->has('demo-plugin'));
        $this->assertTrue($manager->has('resource-plugin'));
    }

    public function test_a_discovered_plugins_routes_are_reachable(): void
    {
        $this->get('/resource-plugin-test/web')->assertOk()->assertSee('ok-web');
    }

    public function test_a_discovered_plugins_views_are_reachable(): void
    {
        $this->assertSame(
            "Hello World\n",
            view('resource-plugin::hello', ['name' => 'World'])->render(),
        );
    }

    public function test_a_discovered_plugins_translations_are_reachable(): void
    {
        $this->assertSame('Welcome!', trans('resource-plugin::messages.welcome'));
    }

    public function test_a_discovered_plugins_commands_are_registered(): void
    {
        $this->artisan('resource-plugin:demo')->assertSuccessful();
    }
}
