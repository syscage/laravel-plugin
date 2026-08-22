<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\PluginCache;

final class PluginCacheTest extends TestCase
{
    private string $cachePath;

    private PluginCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-cache-', true) . DIRECTORY_SEPARATOR . 'plugins.php';
        $this->cache = new PluginCache(new Filesystem(), $this->cachePath);
    }

    protected function tearDown(): void
    {
        $this->cache->forget();

        if (is_dir(dirname($this->cachePath))) {
            rmdir(dirname($this->cachePath));
        }

        parent::tearDown();
    }

    public function test_exists_is_false_before_anything_is_cached(): void
    {
        $this->assertFalse($this->cache->exists());
        $this->assertSame([], $this->cache->get());
    }

    public function test_it_writes_and_reads_back_the_cache(): void
    {
        $data = [
            'demo-plugin' => [
                'manifest' => ['alias' => 'demo-plugin', 'name' => 'DemoPlugin'],
                'path' => '/plugins/demo-plugin',
            ],
        ];

        $this->cache->put($data);

        $this->assertTrue($this->cache->exists());
        $this->assertSame($data, $this->cache->get());
    }

    public function test_forget_removes_the_cache_file(): void
    {
        $this->cache->put(['demo-plugin' => ['manifest' => [], 'path' => '/plugins/demo-plugin']]);
        $this->cache->forget();

        $this->assertFalse($this->cache->exists());
    }
}
