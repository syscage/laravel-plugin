<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginRecordRepository;
use Syscage\Plugin\Tests\TestCase;

final class PluginRecordRepositoryTest extends TestCase
{
    private function attributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'DemoPlugin',
            'alias' => 'demo-plugin',
            'description' => 'A demo plugin.',
            'version' => '1.0.0',
            'provider' => 'Plugins\\DemoPlugin\\Providers\\DemoPluginServiceProvider',
            'namespace' => 'Plugins\\DemoPlugin',
            'priority' => 100,
            'enabled' => false,
            'path' => '/app/plugins/demo-plugin',
        ], $overrides);
    }

    public function test_it_creates_a_plugin_record_with_a_generated_uuid(): void
    {
        $repository = new PluginRecordRepository();

        $record = $repository->create($this->attributes());

        $this->assertNotEmpty($record->uuid);
        $this->assertSame('demo-plugin', $record->alias);
        $this->assertDatabaseHas(config('plugin.table'), ['alias' => 'demo-plugin']);
    }

    public function test_it_finds_a_record_by_alias_and_uuid(): void
    {
        $repository = new PluginRecordRepository();
        $created = $repository->create($this->attributes());

        $this->assertSame($created->id, $repository->findByAlias('demo-plugin')?->id);
        $this->assertSame($created->id, $repository->findByUuid($created->uuid)?->id);
        $this->assertNull($repository->findByAlias('missing-plugin'));
    }

    public function test_exists_reflects_whether_a_record_is_present(): void
    {
        $repository = new PluginRecordRepository();

        $this->assertFalse($repository->exists('demo-plugin'));

        $repository->create($this->attributes());

        $this->assertTrue($repository->exists('demo-plugin'));
    }

    public function test_it_updates_a_record(): void
    {
        $repository = new PluginRecordRepository();
        $record = $repository->create($this->attributes());

        $repository->update($record, ['enabled' => true]);

        $this->assertTrue($repository->findByAlias('demo-plugin')->enabled);
    }

    public function test_it_deletes_a_record(): void
    {
        $repository = new PluginRecordRepository();
        $record = $repository->create($this->attributes());

        $repository->delete($record);

        $this->assertFalse($repository->exists('demo-plugin'));
    }

    public function test_all_returns_every_record(): void
    {
        $repository = new PluginRecordRepository();
        $repository->create($this->attributes());
        $repository->create($this->attributes(['alias' => 'second-plugin', 'name' => 'SecondPlugin']));

        $this->assertCount(2, $repository->all());
    }
}
