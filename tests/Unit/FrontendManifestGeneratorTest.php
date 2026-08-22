<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\FrontendManifestGenerator;
use Syscage\Plugin\Support\PluginRouteFinder;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class FrontendManifestGeneratorTest extends TestCase
{
    private string $pluginPath;

    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('frontend-manifest-plugin-', true);
        mkdir($this->pluginPath . '/resources/js/Pages', recursive: true);
        file_put_contents($this->pluginPath . '/resources/js/Pages/Dashboard.tsx', 'export default function Dashboard() {}');

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('frontend-manifest-cache-', true) . '/plugins.ts';
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->pluginPath);
        (new Filesystem())->deleteDirectory(dirname($this->cachePath));

        parent::tearDown();
    }

    private function makePlugin(array $sidebar = []): FakePlugin
    {
        return new FakePlugin(
            alias: 'demo-plugin',
            basePath: $this->pluginPath,
            sidebarValue: $sidebar,
        );
    }

    public function test_it_generates_page_entries_from_the_plugin_resources_pages_directory(): void
    {
        $router = new Router(new Dispatcher());
        $generator = new FrontendManifestGenerator(new Filesystem(), $router, new PluginRouteFinder(), $this->cachePath);

        $contents = $generator->generate(['demo-plugin' => $this->makePlugin()]);

        $this->assertStringContainsString('"demo-plugin/Dashboard"', $contents);
        $this->assertStringContainsString('import(', $contents);
        $this->assertFileExists($this->cachePath);
    }

    public function test_it_includes_routes_defined_inside_the_plugin_directory(): void
    {
        $controllerFile = $this->pluginPath . '/DashboardController.php';
        file_put_contents($controllerFile, <<<'PHP'
        <?php
        namespace Syscage\Plugin\Tests\Unit\Fixtures\FrontendManifest;
        final class DashboardController
        {
            public function index() {}
        }
        PHP);
        require_once $controllerFile;

        $router = new Router(new Dispatcher());
        $router->get('demo-plugin/dashboard', [
            'uses' => 'Syscage\\Plugin\\Tests\\Unit\\Fixtures\\FrontendManifest\\DashboardController@index',
        ])->name('demo-plugin.dashboard');

        $generator = new FrontendManifestGenerator(new Filesystem(), $router, new PluginRouteFinder(), $this->cachePath);

        $contents = $generator->generate(['demo-plugin' => $this->makePlugin()]);

        $this->assertStringContainsString('"uri": "demo-plugin/dashboard"', $contents);
        $this->assertStringContainsString('"name": "demo-plugin.dashboard"', $contents);
    }

    public function test_it_includes_non_empty_sidebars(): void
    {
        $router = new Router(new Dispatcher());
        $generator = new FrontendManifestGenerator(new Filesystem(), $router, new PluginRouteFinder(), $this->cachePath);

        $contents = $generator->generate([
            'demo-plugin' => $this->makePlugin(['label' => 'Demo', 'icon' => 'cube']),
        ]);

        $this->assertStringContainsString('"demo-plugin"', $contents);
        $this->assertStringContainsString('"label": "Demo"', $contents);
    }
}
