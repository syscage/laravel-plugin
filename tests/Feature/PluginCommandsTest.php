<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Tests\TestCase;

/**
 * Exercises every "plugin:*" management command against disposable copies
 * of the fixture plugins, so lifecycle commands (which write back to
 * "plugin.json" and the database) never mutate the committed fixtures.
 */
final class PluginCommandsTest extends TestCase
{
    private string $pluginsPath;

    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-commands-', true);
        $files = new Filesystem();
        $files->copyDirectory(realpath(__DIR__ . '/../Fixtures/plugins'), $sandbox);

        $this->pluginsPath = $sandbox;
        $this->cacheDirectory = $sandbox . '-cache';

        $app['config']->set('plugin.plugins_path', $this->pluginsPath);
        $app['config']->set('plugin.cache.plugins', $this->cacheDirectory . '/plugins.php');
        $app['config']->set('plugin.cache.sidebar', $this->cacheDirectory . '/sidebar.php');
        $app['config']->set('plugin.cache.frontend', $this->cacheDirectory . '/plugins.ts');
        $app['config']->set('plugin.public_path', $this->cacheDirectory . '/public');
    }

    protected function tearDown(): void
    {
        $files = new Filesystem();
        $files->deleteDirectory($this->pluginsPath);
        $files->deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    public function test_list_shows_every_discovered_plugin(): void
    {
        $this->artisan('plugin:list')
            ->assertSuccessful()
            ->expectsOutputToContain('resource-plugin')
            ->expectsOutputToContain('demo-plugin');
    }

    public function test_discover_rebuilds_the_cache_and_reports_findings(): void
    {
        $this->artisan('plugin:discover')
            ->assertSuccessful()
            ->expectsOutputToContain('Discovered 2 plugins.');

        $this->assertFileExists($this->cacheDirectory . '/plugins.php');
    }

    public function test_cache_builds_discovery_and_sidebar_caches(): void
    {
        $this->artisan('plugin:cache')->assertSuccessful();

        $this->assertFileExists($this->cacheDirectory . '/plugins.php');
        $this->assertFileExists($this->cacheDirectory . '/sidebar.php');
    }

    public function test_cache_skips_the_frontend_manifest_for_a_blade_host(): void
    {
        config(['plugin.frontend' => 'blade']);

        $this->artisan('plugin:cache')->assertSuccessful();

        $this->assertFileDoesNotExist($this->cacheDirectory . '/plugins.ts');
    }

    public function test_cache_builds_the_frontend_manifest_for_a_non_blade_host(): void
    {
        config(['plugin.frontend' => 'react']);

        $this->artisan('plugin:cache')->assertSuccessful();

        $this->assertFileExists($this->cacheDirectory . '/plugins.ts');
        $this->assertStringContainsString(
            'pluginPages',
            file_get_contents($this->cacheDirectory . '/plugins.ts'),
        );
    }

    public function test_clear_removes_compiled_caches(): void
    {
        $this->artisan('plugin:cache')->assertSuccessful();
        $this->artisan('plugin:clear')->assertSuccessful();

        $this->assertFileDoesNotExist($this->cacheDirectory . '/plugins.php');
        $this->assertFileDoesNotExist($this->cacheDirectory . '/sidebar.php');
    }

    public function test_install_then_uninstall_a_plugin(): void
    {
        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('installed');

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'resource-plugin']);

        $manifest = json_decode(file_get_contents($this->pluginsPath . '/resource-plugin/plugin.json'), true);
        $this->assertNotNull($manifest['id']);

        $this->artisan('plugin:uninstall', ['alias' => 'resource-plugin', '--force' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('uninstalled');

        $this->assertDatabaseMissing(config('plugin.table'), ['alias' => 'resource-plugin']);
    }

    public function test_install_twice_fails(): void
    {
        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])->assertSuccessful();

        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])
            ->assertFailed()
            ->expectsOutputToContain('already installed');
    }

    public function test_disable_then_enable_a_plugin(): void
    {
        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])->assertSuccessful();

        $this->artisan('plugin:disable', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('disabled');

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'resource-plugin', 'enabled' => false]);

        $this->artisan('plugin:enable', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('enabled');

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'resource-plugin', 'enabled' => true]);
    }

    public function test_update_reports_when_already_current(): void
    {
        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])->assertSuccessful();

        $this->artisan('plugin:update', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('already at version');
    }

    public function test_update_applies_a_new_manifest_version(): void
    {
        $this->artisan('plugin:install', ['alias' => 'resource-plugin'])->assertSuccessful();

        $manifestPath = $this->pluginsPath . '/resource-plugin/plugin.json';
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $manifest['version'] = '1.1.0';
        file_put_contents($manifestPath, json_encode($manifest));

        $this->artisan('plugin:discover')->assertSuccessful();

        $this->artisan('plugin:update', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('updated from [1.0.0] to [1.1.0]');

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'resource-plugin', 'version' => '1.1.0']);
    }

    public function test_publish_links_a_plugins_assets(): void
    {
        $this->artisan('plugin:publish', ['alias' => 'resource-plugin'])->assertSuccessful();

        $this->assertFileExists($this->cacheDirectory . '/public/resource-plugin/asset.txt');
    }

    public function test_info_displays_plugin_details(): void
    {
        $this->artisan('plugin:info', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('ResourcePlugin')
            ->expectsOutputToContain('resource-plugin');
    }

    public function test_info_fails_for_an_unknown_alias(): void
    {
        $this->artisan('plugin:info', ['alias' => 'does-not-exist'])
            ->assertFailed()
            ->expectsOutputToContain('No plugin registered');
    }

    public function test_doctor_reports_a_healthy_plugin(): void
    {
        $this->artisan('plugin:doctor', ['alias' => 'resource-plugin'])
            ->assertSuccessful()
            ->expectsOutputToContain('All checked plugins are healthy.');
    }
}
