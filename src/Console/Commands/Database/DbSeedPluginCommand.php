<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Delegates to "db:seed", defaulting the seeder class to the target
 * plugin's own "Database\Seeders\DatabaseSeeder".
 */
final class DbSeedPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'db:seed-plugin
        {plugin : The plugin to seed}
        {--class= : The class name of the seeder; defaults to the plugin\'s own DatabaseSeeder}
        {--database= : The database connection to seed}
        {--force : Force the operation to run when in production}';

    protected $description = 'Seed a plugin\'s database using its own DatabaseSeeder';

    public function handle(): int
    {
        $plugin = $this->targetPlugin();

        $class = $this->option('class')
            ?? rtrim($plugin->namespace(), '\\') . '\\Database\\Seeders\\DatabaseSeeder';

        return $this->call('db:seed', array_filter([
            '--class' => $class,
            '--database' => $this->option('database'),
            '--force' => $this->option('force'),
        ], static fn (mixed $value): bool => $value !== null && $value !== false));
    }
}
