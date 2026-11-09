<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\InvalidPluginManifestException;
use Syscage\Plugin\Support\PluginManifest;

final class PluginManifestTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'id' => '1ca65e6b-51dc-40e8-a272-964f5d3cd5a0',
            'name' => 'MyPlugin',
            'alias' => 'my-plugin',
            'version' => '1.0.0',
            'provider' => 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider',
            'namespace' => 'Plugins\\MyPlugin',
            'requires' => [],
            'authors' => [],
            'table_prefix' => null,
        ];
    }

    public function test_it_builds_from_a_complete_array(): void
    {
        $manifest = PluginManifest::fromArray($this->validData());

        $this->assertSame('1ca65e6b-51dc-40e8-a272-964f5d3cd5a0', $manifest->id());
        $this->assertSame('MyPlugin', $manifest->name());
        $this->assertSame('my-plugin', $manifest->alias());
        $this->assertSame($this->validData(), $manifest->toArray());
    }

    public function test_it_fills_defaults_for_optional_fields(): void
    {
        $manifest = PluginManifest::fromArray([
            'name' => 'MyPlugin',
            'alias' => 'my-plugin',
            'version' => '1.0.0',
            'provider' => 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider',
            'namespace' => 'Plugins\\MyPlugin',
        ]);

        $this->assertNull($manifest->id());
        $this->assertSame([], $manifest->requires());
        $this->assertNull($manifest->tablePrefix());
    }

    public function test_it_reads_a_table_prefix_override(): void
    {
        $manifest = PluginManifest::fromArray([...$this->validData(), 'table_prefix' => 'my_plugin_']);

        $this->assertSame('my_plugin_', $manifest->tablePrefix());
    }

    public function test_it_throws_when_a_required_field_is_missing(): void
    {
        $this->expectException(InvalidPluginManifestException::class);

        PluginManifest::fromArray([
            'alias' => 'my-plugin',
            'version' => '1.0.0',
            'provider' => 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider',
            'namespace' => 'Plugins\\MyPlugin',
        ]);
    }

    public function test_with_id_returns_a_new_instance_without_mutating_the_original(): void
    {
        $manifest = PluginManifest::fromArray($this->validData());
        $updated = $manifest->withId('new-id');

        $this->assertSame('1ca65e6b-51dc-40e8-a272-964f5d3cd5a0', $manifest->id());
        $this->assertSame('new-id', $updated->id());
    }
}
