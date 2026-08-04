<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\PluginAssetManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginAssetManagerTest extends TestCase
{
    use ResourcePluginFixture;

    private string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->publicPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-assets-', true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function test_it_links_the_plugin_public_directory(): void
    {
        $manager = new PluginAssetManager(new Filesystem(), $this->publicPath);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        $linked = $this->publicPath . DIRECTORY_SEPARATOR . $plugin->alias() . DIRECTORY_SEPARATOR . 'asset.txt';

        $this->assertFileExists($linked);
        $this->assertSame("asset content\n", file_get_contents($linked));
    }

    public function test_it_does_not_fail_when_called_twice(): void
    {
        $manager = new PluginAssetManager(new Filesystem(), $this->publicPath);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);
        $manager->register($plugin);

        $this->assertFileExists(
            $this->publicPath . DIRECTORY_SEPARATOR . $plugin->alias() . DIRECTORY_SEPARATOR . 'asset.txt',
        );
    }
}
