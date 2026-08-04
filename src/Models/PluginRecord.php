<?php

declare(strict_types=1);

namespace Syscage\Plugin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Eloquent model for the "plugins" table, tracking the installation state
 * of each plugin (as opposed to `Syscage\Plugin\Contracts\PluginInterface`,
 * which represents a plugin's manifest and filesystem layout).
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $alias
 * @property string|null $description
 * @property string $version
 * @property string $provider
 * @property string $namespace
 * @property int $priority
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $installed_at
 * @property \Illuminate\Support\Carbon|null $enabled_at
 * @property \Illuminate\Support\Carbon|null $disabled_at
 * @property string $path
 * @property string|null $hash
 */
final class PluginRecord extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'alias',
        'description',
        'version',
        'provider',
        'namespace',
        'priority',
        'enabled',
        'installed_at',
        'enabled_at',
        'disabled_at',
        'path',
        'hash',
    ];

    protected $casts = [
        'priority' => 'integer',
        'enabled' => 'boolean',
        'installed_at' => 'datetime',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('plugin.table', 'plugins');
    }

    protected static function booted(): void
    {
        static::creating(function (self $record): void {
            if (! $record->uuid) {
                $record->uuid = (string) Str::uuid();
            }
        });
    }
}
