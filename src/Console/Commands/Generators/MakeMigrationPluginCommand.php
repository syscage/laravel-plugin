<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Delegates to Laravel's own "make:migration", redirecting its output into
 * the target plugin's "database/migrations" directory via the "--path" and
 * "--realpath" options it already supports.
 */
final class MakeMigrationPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'make:migration-plugin
        {plugin : The plugin to generate into}
        {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}';

    protected $description = 'Create a new migration file inside a plugin';

    public function handle(): int
    {
        return $this->call('make:migration', [
            'name' => $this->argument('name'),
            '--create' => $this->prefixedTableOption($this->option('create')),
            '--table' => $this->prefixedTableOption($this->option('table')),
            '--path' => $this->targetPlugin()->migrationPath(),
            '--realpath' => true,
        ]);
    }

    /**
     * Apply the target plugin's table prefix to a "--create"/"--table"
     * option value, leaving an absent option untouched.
     */
    private function prefixedTableOption(?string $table): ?string
    {
        return $table !== null && $table !== '' ? $this->prefixedPluginTable($table) : $table;
    }
}
