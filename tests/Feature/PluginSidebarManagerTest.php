<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Syscage\Plugin\PluginSidebarManager;
use Syscage\Plugin\Tests\Support\FakePlugin;
use Syscage\Plugin\Tests\TestCase;

final class PluginSidebarManagerTest extends TestCase
{
    private string $cachePath;

    private PluginSidebarManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-sidebar-', true) . '.php';
        $this->manager = new PluginSidebarManager(
            $this->app->make(ConnectionInterface::class),
            new Filesystem(),
            $this->cachePath,
        );
    }

    protected function tearDown(): void
    {
        $this->manager->forget();

        parent::tearDown();
    }

    private function seedItem(string $plugin, string $label, int $sort = 0): void
    {
        $this->app->make(ConnectionInterface::class)->table('sidebar')->insert([
            'id' => (string) Str::uuid(),
            'plugin' => $plugin,
            'label' => $label,
            'sort' => $sort,
        ]);
    }

    public function test_build_groups_sidebar_rows_by_plugin_alias_ordered_by_sort(): void
    {
        $this->seedItem('shop', 'Orders', 2);
        $this->seedItem('shop', 'Products', 1);
        $this->seedItem('other', 'Ignored');

        $sidebar = $this->manager->build([
            'shop' => new FakePlugin('shop'),
            'blog' => new FakePlugin('blog'),
        ]);

        $this->assertSame(['shop'], array_keys($sidebar));
        $this->assertSame(['Products', 'Orders'], array_column($sidebar['shop'], 'label'));
    }

    public function test_cache_persists_and_get_reads_it_back_and_forget_removes_it(): void
    {
        $this->seedItem('shop', 'Products');

        $this->manager->cache(['shop' => new FakePlugin('shop')]);

        $this->assertSame('Products', $this->manager->get()['shop'][0]['label']);

        $this->manager->forget();

        $this->assertSame([], $this->manager->get());
    }
}
