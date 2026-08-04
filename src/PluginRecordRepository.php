<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Support\Collection;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Models\PluginRecord;

/**
 * Eloquent-backed implementation of {@see PluginRecordRepositoryInterface}.
 */
final class PluginRecordRepository implements PluginRecordRepositoryInterface
{
    public function findByAlias(string $alias): ?PluginRecord
    {
        return PluginRecord::query()->where('alias', $alias)->first();
    }

    public function findByUuid(string $uuid): ?PluginRecord
    {
        return PluginRecord::query()->where('uuid', $uuid)->first();
    }

    public function exists(string $alias): bool
    {
        return PluginRecord::query()->where('alias', $alias)->exists();
    }

    public function create(array $attributes): PluginRecord
    {
        return PluginRecord::query()->create($attributes);
    }

    public function update(PluginRecord $record, array $attributes): PluginRecord
    {
        $record->fill($attributes)->save();

        return $record;
    }

    public function delete(PluginRecord $record): void
    {
        $record->delete();
    }

    public function all(): Collection
    {
        return PluginRecord::query()->get();
    }
}
