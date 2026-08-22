<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Delegates to "db:wipe". Unlike the other database "-plugin" aliases,
 * "db:wipe" has no path/class concept to scope by plugin — dropping all
 * tables is inherently a whole-database operation — so this alias exists
 * for command-family consistency and forwards straight through.
 */
final class DbWipePluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'db:wipe-plugin
        {plugin : Accepted for consistency with the other database "-plugin" commands; has no effect}
        {--database= : The database connection to use}
        {--drop-views : Drop all tables and views}
        {--drop-types : Drop all tables and types (Postgres only)}
        {--force : Force the operation to run when in production}';

    protected $description = 'Drop all tables, views, and types (identical to "db:wipe")';

    public function handle(): int
    {
        // Resolving the plugin still validates the argument even though it
        // does not affect the (inherently global) wipe operation.
        $this->targetPlugin();

        return $this->call('db:wipe', array_filter([
            '--database' => $this->option('database'),
            '--drop-views' => $this->option('drop-views'),
            '--drop-types' => $this->option('drop-types'),
            '--force' => $this->option('force'),
        ], static fn (mixed $value): bool => $value !== null && $value !== false));
    }
}
