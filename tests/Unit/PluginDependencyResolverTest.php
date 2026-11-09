<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Exceptions\CircularPluginDependencyException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\PluginDependencyResolver;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginDependencyResolverTest extends TestCase
{
    /**
     * @param array<int, string> $enabled
     */
    private function makeResolver(array $enabled = ['a', 'b', 'c', 'core', 'shop']): PluginDependencyResolver
    {
        $records = $this->createStub(PluginRecordRepositoryInterface::class);
        $records->method('enabledAliases')->willReturn($enabled);

        return new PluginDependencyResolver($records);
    }

    public function test_it_orders_independent_plugins_by_alias(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'b' => new FakePlugin('b'),
            'a' => new FakePlugin('a'),
            'c' => new FakePlugin('c'),
        ];

        $this->assertSame(['a', 'b', 'c'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_orders_dependencies_before_dependents(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: ['core']),
            'core' => new FakePlugin('core'),
        ];

        $this->assertSame(['core', 'shop'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_throws_when_a_required_plugin_is_missing(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: ['core']),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_throws_when_a_required_plugin_is_disabled(): void
    {
        $resolver = $this->makeResolver(['shop']);

        $plugins = [
            'shop' => new FakePlugin('shop', requires: ['core']),
            'core' => new FakePlugin('core'),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_validates_version_constraints(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: [['alias' => 'core', 'version' => '^2.0']]),
            'core' => new FakePlugin('core', version: '1.5.0'),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_accepts_satisfied_version_constraints(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: [['alias' => 'core', 'version' => '^2.0']]),
            'core' => new FakePlugin('core', version: '2.3.0'),
        ];

        $this->assertSame(['core', 'shop'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_throws_on_circular_dependencies(): void
    {
        $resolver = $this->makeResolver();

        $plugins = [
            'a' => new FakePlugin('a', requires: ['b']),
            'b' => new FakePlugin('b', requires: ['a']),
        ];

        $this->expectException(CircularPluginDependencyException::class);

        $resolver->resolve($plugins);
    }
}
