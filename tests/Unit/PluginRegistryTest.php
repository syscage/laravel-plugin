<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;
use Syscage\Plugin\PluginRegistry;

final class PluginRegistryTest extends TestCase
{
    private function makePlugin(string $alias, int $priority, bool $enabled = true): PluginInterface
    {
        $plugin = $this->createStub(PluginInterface::class);
        $plugin->method('alias')->willReturn($alias);
        $plugin->method('priority')->willReturn($priority);
        $plugin->method('enabled')->willReturn($enabled);

        return $plugin;
    }

    public function test_it_registers_and_retrieves_plugins_by_alias(): void
    {
        $registry = new PluginRegistry();
        $plugin = $this->makePlugin('demo-plugin', 100);

        $registry->register($plugin);

        $this->assertTrue($registry->has('demo-plugin'));
        $this->assertSame($plugin, $registry->get('demo-plugin'));
        $this->assertSame(['demo-plugin' => $plugin], $registry->all());
    }

    public function test_get_throws_when_the_alias_is_unknown(): void
    {
        $registry = new PluginRegistry();

        $this->expectException(PluginNotFoundException::class);

        $registry->get('missing-plugin');
    }

    public function test_forget_removes_a_plugin(): void
    {
        $registry = new PluginRegistry();
        $registry->register($this->makePlugin('demo-plugin', 100));

        $registry->forget('demo-plugin');

        $this->assertFalse($registry->has('demo-plugin'));
    }

    public function test_enabled_excludes_disabled_plugins_and_sorts_by_priority(): void
    {
        $registry = new PluginRegistry();
        $registry->register($this->makePlugin('low-priority', 200));
        $registry->register($this->makePlugin('disabled', 50, enabled: false));
        $registry->register($this->makePlugin('high-priority', 10));

        $this->assertSame(['high-priority', 'low-priority'], array_keys($registry->enabled()));
    }
}
