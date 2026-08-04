<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Illuminate\Support\Collection;
use Syscage\Plugin\Models\PluginRecord;

/**
 * Persists and retrieves the installation state of plugins from the
 * database.
 */
interface PluginRecordRepositoryInterface
{
    /**
     * Find a plugin's database record by its alias.
     */
    public function findByAlias(string $alias): ?PluginRecord;

    /**
     * Find a plugin's database record by its UUID.
     */
    public function findByUuid(string $uuid): ?PluginRecord;

    /**
     * Determine whether a plugin with the given alias has a database record.
     */
    public function exists(string $alias): bool;

    /**
     * Create a new plugin database record.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): PluginRecord;

    /**
     * Update an existing plugin database record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(PluginRecord $record, array $attributes): PluginRecord;

    /**
     * Delete a plugin database record.
     */
    public function delete(PluginRecord $record): void;

    /**
     * All plugin database records.
     *
     * @return Collection<int, PluginRecord>
     */
    public function all(): Collection;
}
