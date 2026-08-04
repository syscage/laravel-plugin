<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigrateFreshPluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate:fresh-plugin
        {plugin : The plugin whose migrations should run}
        {--database= : The database connection to use}
        {--drop-views : Drop all tables and views}
        {--drop-types : Drop all tables and types (Postgres only)}
        {--force : Force the operation to run when in production}
        {--schema-path= : The path to a schema dump file}
        {--seed : Indicates if the seed task should be re-run}
        {--seeder= : The class name of the root seeder}
        {--step : Force the migrations to be run so they can be rolled back individually}';

    protected $description = "Drop all tables in the database (exactly like \"migrate:fresh\") and re-run only the given plugin's migrations";

    protected function delegateCommand(): string
    {
        return 'migrate:fresh';
    }
}
