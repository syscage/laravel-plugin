<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigrateResetPluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate:reset-plugin
        {plugin : The plugin whose migrations should be reset}
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--pretend : Dump the SQL queries that would be run}';

    protected $description = "Rollback all of a plugin's migrations";

    protected function delegateCommand(): string
    {
        return 'migrate:reset';
    }
}
