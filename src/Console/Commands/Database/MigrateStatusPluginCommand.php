<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigrateStatusPluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate:status-plugin
        {plugin : The plugin whose migration status should be shown}
        {--database= : The database connection to use}
        {--pending : Only list pending migrations}';

    protected $description = "Show the status of a plugin's migrations";

    protected function delegateCommand(): string
    {
        return 'migrate:status';
    }
}
