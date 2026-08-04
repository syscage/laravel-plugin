<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Tests\TestCase;

final class MakePluginCommandTest extends TestCase
{
    private string $pluginsPath;

    private string $cacheDirectory;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $this->pluginsPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('make-plugin-', true);
        $this->cacheDirectory = $this->pluginsPath . '-cache';

        $app['config']->set('plugin.plugins_path', $this->pluginsPath);
        $app['config']->set('plugin.namespace_root', 'GeneratedPlugins');
        $app['config']->set('plugin.cache.plugins', $this->cacheDirectory . '/plugins.php');
        $app['config']->set('plugin.cache.sidebar', $this->cacheDirectory . '/sidebar.php');
    }

    protected function tearDown(): void
    {
        $files = new Filesystem();
        $files->deleteDirectory($this->pluginsPath);
        $files->deleteDirectory($this->cacheDirectory);

        parent::tearDown();
    }

    public function test_it_scaffolds_the_full_plugin_directory_structure(): void
    {
        $this->artisan('make:plugin', [
            'name' => 'BlogPlugin',
            '--description' => 'Blog Integration',
            '--author' => 'Syscage',
        ])->assertSuccessful();

        $base = $this->pluginsPath . '/blog-plugin';

        foreach ([
            '/plugin.json',
            '/composer.json',
            '/README.md',
            '/src/Plugin.php',
            '/src/app/Providers/BlogPluginPluginServiceProvider.php',
            '/src/routes/web.php',
            '/src/routes/api.php',
            '/src/routes/console.php',
            '/src/config/config.php',
            '/src/database/seeders/DatabaseSeeder.php',
        ] as $expected) {
            $this->assertFileExists($base . $expected, "Expected file [{$expected}] to exist.");
        }

        foreach ([
            '/src/app/Http/Controllers',
            '/src/app/Http/Middleware',
            '/src/app/Http/Requests',
            '/src/app/Models',
            '/src/database/migrations',
            '/src/database/factories',
            '/src/database/seeders',
            '/src/lang',
            '/src/public',
            '/src/resources/css',
            '/src/resources/js',
            '/src/resources/views',
        ] as $expected) {
            $this->assertDirectoryExists($base . $expected, "Expected directory [{$expected}] to exist.");
        }
    }

    public function test_the_generated_manifest_is_valid_and_correctly_derived(): void
    {
        $this->artisan('make:plugin', ['name' => 'BlogPlugin', '--description' => 'Blog Integration'])
            ->assertSuccessful();

        $manifest = json_decode(file_get_contents($this->pluginsPath . '/blog-plugin/plugin.json'), true);

        $this->assertNull($manifest['id']);
        $this->assertSame('BlogPlugin', $manifest['name']);
        $this->assertSame('blog-plugin', $manifest['alias']);
        $this->assertSame('Blog Integration', $manifest['description']);
        $this->assertSame('1.0.0', $manifest['version']);
        $this->assertSame('GeneratedPlugins\\BlogPlugin', $manifest['namespace']);
        $this->assertSame(
            'GeneratedPlugins\\BlogPlugin\\Providers\\BlogPluginPluginServiceProvider',
            $manifest['provider'],
        );
        $this->assertTrue($manifest['enabled']);
    }

    public function test_the_generated_plugin_class_is_immediately_discoverable(): void
    {
        // make:plugin invalidates the discovery cache; a fresh "php artisan"
        // process would naturally re-scan on its next boot. Within a single
        // test, the already-booted app's in-memory registry is stale until
        // something re-triggers discovery — here, "plugin:discover", just
        // as a user chaining commands in one shell session would run.
        $this->artisan('make:plugin', ['name' => 'BlogPlugin'])->assertSuccessful();
        $this->artisan('plugin:discover')->assertSuccessful();

        $this->artisan('plugin:list')
            ->assertSuccessful()
            ->expectsOutputToContain('blog-plugin');

        $this->artisan('plugin:install', ['alias' => 'blog-plugin'])->assertSuccessful();

        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'blog-plugin']);
    }

    public function test_it_fails_when_the_plugin_already_exists(): void
    {
        $this->artisan('make:plugin', ['name' => 'BlogPlugin'])->assertSuccessful();

        $this->artisan('make:plugin', ['name' => 'BlogPlugin'])
            ->assertFailed()
            ->expectsOutputToContain('already exists');
    }
}
