<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Syscage\Plugin\Contracts\PluginRecordRepositoryInterface;
use Syscage\Plugin\Models\PluginRecord;

/**
 * Eloquent-backed implementation of {@see PluginRecordRepositoryInterface},
 * querying whichever model class the host application has configured under
 * "plugin.model" (defaulting to {@see PluginRecord} itself).
 */
final class PluginRecordRepository implements PluginRecordRepositoryInterface
{
    public function findByAlias(string $alias): ?PluginRecord
    {
        return $this->newQuery()->where('alias', $alias)->first();
    }

    public function findByUuid(string $uuid): ?PluginRecord
    {
        return $this->newQuery()->where('uuid', $uuid)->first();
    }

    public function exists(string $alias): bool
    {
        return $this->newQuery()->where('alias', $alias)->exists();
    }

    public function create(array $attributes): PluginRecord
    {
        return $this->newQuery()->create($attributes);
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
        return $this->newQuery()->get();
    }

    /**
     * @return Builder<PluginRecord>
     */
    private function newQuery(): Builder
    {
        $model = $this->modelClass();

        return (new $model())->newQuery();
    }

    /**
     * @return class-string<PluginRecord>
     */
    private function modelClass(): string
    {
        return (string) config('plugin.model', PluginRecord::class);
    }
}
