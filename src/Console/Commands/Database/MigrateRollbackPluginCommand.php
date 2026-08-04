<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesWithMigrationPath;

final class MigrateRollbackPluginCommand extends Command
{
    use DelegatesWithMigrationPath;

    protected $signature = 'migrate:rollback-plugin
        {plugin : The plugin whose migrations should be rolled back}
        {--database= : The database connection to use}
        {--force : Force the operation to run when in production}
        {--pretend : Dump the SQL queries that would be run}
        {--step= : The number of migrations to be reverted}
        {--batch= : The batch of migrations (identified by their batch number) to be reverted}';

    protected $description = "Rollback a plugin's last database migration";

    protected function delegateCommand(): string
    {
        return 'migrate:rollback';
    }
}
