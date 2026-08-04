<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\PluginSidebarManager;

final class PluginSidebarManagerTest extends TestCase
{
    private string $cachePath;

    private PluginSidebarManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-sidebar-', true) . '.php';
        $this->manager = new PluginSidebarManager(new Filesystem(), $this->cachePath);
    }

    protected function tearDown(): void
    {
        $this->manager->forget();

        parent::tearDown();
    }

    private function pluginWithSidebar(string $alias, array $sidebar): \Syscage\Plugin\Contracts\PluginInterface
    {
        $plugin = $this->createStub(\Syscage\Plugin\Contracts\PluginInterface::class);
        $plugin->method('alias')->willReturn($alias);
        $plugin->method('sidebar')->willReturn($sidebar);

        return $plugin;
    }

    public function test_build_merges_non_empty_sidebars_keyed_by_alias(): void
    {
        $plugins = [
            'shop' => $this->pluginWithSidebar('shop', ['label' => 'Shop', 'icon' => 'cart']),
            'blog' => $this->pluginWithSidebar('blog', []),
        ];

        $sidebar = $this->manager->build($plugins);

        $this->assertSame(['shop' => ['label' => 'Shop', 'icon' => 'cart']], $sidebar);
    }

    public function test_cache_persists_and_get_reads_it_back(): void
    {
        $plugins = ['shop' => $this->pluginWithSidebar('shop', ['label' => 'Shop'])];

        $this->manager->cache($plugins);

        $this->assertSame(['shop' => ['label' => 'Shop']], $this->manager->get());
    }

    public function test_get_returns_empty_array_when_nothing_is_cached(): void
    {
        $this->assertSame([], $this->manager->get());
    }

    public function test_forget_removes_the_cache(): void
    {
        $this->manager->cache(['shop' => $this->pluginWithSidebar('shop', ['label' => 'Shop'])]);
        $this->manager->forget();

        $this->assertSame([], $this->manager->get());
    }
}
