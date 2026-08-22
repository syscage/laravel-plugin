<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigrateRefreshPluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate:refresh-plugin
        {plugin : The plugin whose migrations should be refreshed}
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--seed : Indicates if the seed task should be re-run}
        {--seeder= : The class name of the root seeder}
        {--step= : The number of migrations to be reverted & re-run}';

    protected $description = "Reset and re-run a plugin's migrations";

    protected function delegateCommand(): string
    {
        return 'migrate:refresh';
    }
}
