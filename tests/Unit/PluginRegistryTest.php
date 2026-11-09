<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Exceptions\PluginNotFoundException;
use Syscage\Plugin\PluginRegistry;

final class PluginRegistryTest extends TestCase
{
    /**
     * @param array<int, string> $enabledAliases
     */
    private function makeRegistry(array $enabledAliases = []): PluginRegistry
    {
        $records = $this->createStub(PluginRecordRepositoryInterface::class);
        $records->method('enabledAliases')->willReturn($enabledAliases);

        return new PluginRegistry($records);
    }

    private function makePlugin(string $alias): PluginInterface
    {
        $plugin = $this->createStub(PluginInterface::class);
        $plugin->method('alias')->willReturn($alias);

        return $plugin;
    }

    public function test_it_registers_and_retrieves_plugins_by_alias(): void
    {
        $registry = $this->makeRegistry();
        $plugin = $this->makePlugin('demo-plugin');

        $registry->register($plugin);

        $this->assertTrue($registry->has('demo-plugin'));
        $this->assertSame($plugin, $registry->get('demo-plugin'));
        $this->assertSame(['demo-plugin' => $plugin], $registry->all());
    }

    public function test_get_throws_when_the_alias_is_unknown(): void
    {
        $registry = $this->makeRegistry();

        $this->expectException(PluginNotFoundException::class);

        $registry->get('missing-plugin');
    }

    public function test_forget_removes_a_plugin(): void
    {
        $registry = $this->makeRegistry();
        $registry->register($this->makePlugin('demo-plugin'));

        $registry->forget('demo-plugin');

        $this->assertFalse($registry->has('demo-plugin'));
    }

    public function test_enabled_keeps_only_plugins_with_an_enabled_record_sorted_by_alias(): void
    {
        $registry = $this->makeRegistry(['zeta', 'alpha']);
        $registry->register($this->makePlugin('zeta'));
        $registry->register($this->makePlugin('disabled'));
        $registry->register($this->makePlugin('alpha'));

        $this->assertSame(['alpha', 'zeta'], array_keys($registry->enabled()));
    }
}
