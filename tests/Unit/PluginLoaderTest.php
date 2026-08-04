<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\PluginProviderNotFoundException;
use Syscage\Plugin\PluginLoader;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginLoaderTest extends TestCase
{
    public function test_it_registers_the_provider_of_an_enabled_plugin(): void
    {
        $app = $this->createMock(Application::class);
        $plugin = new FakePlugin('demo-plugin', enabled: true);

        $app->expects($this->once())
            ->method('register')
            ->with($plugin->provider());

        (new PluginLoader($app))->load($plugin);
    }

    public function test_it_skips_disabled_plugins(): void
    {
        $app = $this->createMock(Application::class);
        $plugin = new FakePlugin('demo-plugin', enabled: false);

        $app->expects($this->never())->method('register');

        (new PluginLoader($app))->load($plugin);
    }

    public function test_it_throws_when_the_provider_class_does_not_exist(): void
    {
        $app = $this->createMock(Application::class);

        $plugin = new FakePlugin('demo-plugin', providerOverride: 'Syscage\\Plugin\\Tests\\Fixtures\\DoesNotExist');

        $this->expectException(PluginProviderNotFoundException::class);

        (new PluginLoader($app))->load($plugin);
    }

    public function test_load_many_loads_every_enabled_plugin_in_order(): void
    {
        $app = $this->createMock(Application::class);
        $first = new FakePlugin('first');
        $second = new FakePlugin('second');

        $app->expects($this->exactly(2))->method('register');

        (new PluginLoader($app))->loadMany(['first' => $first, 'second' => $second]);
    }
}
