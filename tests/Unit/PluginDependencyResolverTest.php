<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\CircularPluginDependencyException;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;
use Syscage\Plugin\PluginDependencyResolver;
use Syscage\Plugin\Tests\Support\FakePlugin;

final class PluginDependencyResolverTest extends TestCase
{
    public function test_it_orders_independent_plugins_by_priority(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'b' => new FakePlugin('b', priority: 200),
            'a' => new FakePlugin('a', priority: 10),
            'c' => new FakePlugin('c', priority: 100),
        ];

        $this->assertSame(['a', 'c', 'b'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_orders_dependencies_before_dependents(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', priority: 10, requires: ['core']),
            'core' => new FakePlugin('core', priority: 500),
        ];

        $this->assertSame(['core', 'shop'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_throws_when_a_required_plugin_is_missing(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: ['core']),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_throws_when_a_required_plugin_is_disabled(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: ['core']),
            'core' => new FakePlugin('core', enabled: false),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_validates_version_constraints(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: [['alias' => 'core', 'version' => '^2.0']]),
            'core' => new FakePlugin('core', version: '1.5.0'),
        ];

        $this->expectException(MissingPluginDependencyException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_accepts_satisfied_version_constraints(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', requires: [['alias' => 'core', 'version' => '^2.0']]),
            'core' => new FakePlugin('core', version: '2.3.0'),
        ];

        $this->assertSame(['core', 'shop'], array_keys($resolver->resolve($plugins)));
    }

    public function test_it_throws_on_conflicting_plugins(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'shop' => new FakePlugin('shop', conflicts: ['legacy-shop']),
            'legacy-shop' => new FakePlugin('legacy-shop'),
        ];

        $this->expectException(ConflictingPluginException::class);

        $resolver->resolve($plugins);
    }

    public function test_it_throws_on_circular_dependencies(): void
    {
        $resolver = new PluginDependencyResolver();

        $plugins = [
            'a' => new FakePlugin('a', requires: ['b']),
            'b' => new FakePlugin('b', requires: ['a']),
        ];

        $this->expectException(CircularPluginDependencyException::class);

        $resolver->resolve($plugins);
    }
}
