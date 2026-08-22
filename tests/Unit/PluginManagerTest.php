<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginDiscoveryInterface;
use Syscage\Plugin\Contracts\PluginLoaderInterface;
use Syscage\Plugin\Contracts\PluginRegistryInterface;
use Syscage\Plugin\PluginManager;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginManagerTest extends TestCase
{
    public function test_boot_discovers_resolves_and_loads_enabled_plugins(): void
    {
        $discovery = $this->createMock(PluginDiscoveryInterface::class);
        $registry = $this->createMock(PluginRegistryInterface::class);
        $resolver = $this->createMock(PluginDependencyResolverInterface::class);
        $loader = $this->createMock(PluginLoaderInterface::class);

        $enabled = ['demo-plugin' => new FakePlugin('demo-plugin')];
        $ordered = $enabled;

        $discovery->expects($this->once())->method('discover')->with(true);
        $registry->expects($this->once())->method('enabled')->willReturn($enabled);
        $resolver->expects($this->once())->method('resolve')->with($enabled)->willReturn($ordered);
        $loader->expects($this->once())->method('loadMany')->with($ordered);

        $manager = new PluginManager($discovery, $registry, $resolver, $loader);

        $manager->boot(fresh: true);
    }

    public function test_it_delegates_queries_to_the_registry(): void
    {
        $discovery = $this->createStub(PluginDiscoveryInterface::class);
        $registry = $this->createMock(PluginRegistryInterface::class);
        $resolver = $this->createStub(PluginDependencyResolverInterface::class);
        $loader = $this->createStub(PluginLoaderInterface::class);

        $plugin = new FakePlugin('demo-plugin');

        $registry->method('all')->willReturn(['demo-plugin' => $plugin]);
        $registry->method('enabled')->willReturn(['demo-plugin' => $plugin]);
        $registry->method('has')->with('demo-plugin')->willReturn(true);
        $registry->method('get')->with('demo-plugin')->willReturn($plugin);

        $manager = new PluginManager($discovery, $registry, $resolver, $loader);

        $this->assertSame(['demo-plugin' => $plugin], $manager->all());
        $this->assertSame(['demo-plugin' => $plugin], $manager->enabled());
        $this->assertTrue($manager->has('demo-plugin'));
        $this->assertSame($plugin, $manager->find('demo-plugin'));
    }
}
