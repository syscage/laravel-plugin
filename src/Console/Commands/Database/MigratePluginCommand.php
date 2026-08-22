<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigratePluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate-plugin
        {plugin : The plugin whose migrations should run}
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--schema-path= : The path to a schema dump file}
        {--pretend : Dump the SQL queries that would be run}
        {--seed : Indicates if the seed task should be re-run}
        {--seeder= : The class name of the root seeder}
        {--step : Force the migrations to be run so they can be rolled back individually}
        {--graceful : Return a successful exit code even if an error occurs}';

    protected $description = "Run a plugin's database migrations";

    protected function delegateCommand(): string
    {
        return 'migrate';
    }
}
