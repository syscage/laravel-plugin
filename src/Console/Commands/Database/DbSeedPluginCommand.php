<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;
use Syscage\Plugin\Contracts\PluginSeederManagerInterface;

/**
 * Delegates to "db:seed", defaulting the seeder class to the target
 * plugin's own "Database\Seeders\DatabaseSeeder". With no plugin given,
 * seeds every enabled plugin.
 */
final class DbSeedPluginCommand extends Command
{
    use ConfirmableTrait;
    use ResolvesTargetPlugin;

    protected $signature = 'db:seed-plugin
        {plugin? : The plugin to seed; omit to seed every enabled plugin}
        {--class= : The class name of the seeder; defaults to the plugin\'s own DatabaseSeeder}
        {--database= : The database connection to seed}
        {--force : Force the operation to run when in production}';

    protected $description = 'Seed a plugin\'s database (or every enabled plugin\'s) using its own DatabaseSeeder';

    public function handle(PluginSeederManagerInterface $seeders): int
    {
        if ($this->argument('plugin') === null) {
            return $this->seedAll($seeders);
        }

        $plugin = $this->targetPlugin();

        $class = $this->option('class');

        if ($class === null) {
            $class = rtrim($plugin->namespace(), '\\') . '\\Database\\Seeders\\DatabaseSeeder';

            if (! class_exists($class)) {
                $this->components->info("Plugin [{$plugin->alias()}] has no DatabaseSeeder to run.");

                return self::SUCCESS;
            }
        }

        return $this->call('db:seed', array_filter([
            '--class' => $class,
            '--database' => $this->option('database'),
            '--force' => $this->option('force'),
        ], static fn (mixed $value): bool => $value !== null && $value !== false));
    }

    private function seedAll(PluginSeederManagerInterface $seeders): int
    {
        if ($this->option('class') !== null) {
            $this->components->error('The --class option requires a plugin argument.');

            return self::FAILURE;
        }

        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $seeded = $seeders->seedEnabled($this->option('database') ?: null, $this->output->getOutput());

        if ($seeded === []) {
            $this->components->info('No enabled plugin has a DatabaseSeeder to run.');
        }

        return self::SUCCESS;
    }
}
