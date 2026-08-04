<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\PluginAutoloader;
use Syscage\Plugin\PluginCache;
use Syscage\Plugin\PluginDiscovery;
use Syscage\Plugin\PluginManifestRepository;
use Syscage\Plugin\PluginRegistry;
use Syscage\Plugin\Tests\Fixtures\DemoPlugin\Plugin as DemoPlugin;

final class PluginDiscoveryTest extends TestCase
{
    private string $cachePath;

    private PluginDiscovery $discovery;

    private PluginRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-discovery-', true) . '.php';
        $this->registry = new PluginRegistry();

        $this->discovery = new PluginDiscovery(
            new Filesystem(),
            new PluginManifestRepository(new Filesystem()),
            new PluginAutoloader(),
            new PluginCache(new Filesystem(), $this->cachePath),
            $this->registry,
            __DIR__ . '/../Fixtures/plugins',
            'plugin.json',
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->cachePath);

        parent::tearDown();
    }

    public function test_it_discovers_plugins_from_the_filesystem(): void
    {
        $plugins = $this->discovery->discover(fresh: true);

        $this->assertArrayHasKey('demo-plugin', $plugins);
        $this->assertInstanceOf(DemoPlugin::class, $plugins['demo-plugin']);
        $this->assertSame('DemoPlugin', $plugins['demo-plugin']->name());
    }

    public function test_it_populates_the_registry(): void
    {
        $this->discovery->discover(fresh: true);

        $this->assertTrue($this->registry->has('demo-plugin'));
    }

    public function test_it_writes_and_then_reuses_the_cache(): void
    {
        $fromFilesystem = $this->discovery->discover(fresh: true);
        $fromCache = $this->discovery->discover(fresh: false);

        $this->assertSame(
            $fromFilesystem['demo-plugin']->alias(),
            $fromCache['demo-plugin']->alias(),
        );
    }
}
