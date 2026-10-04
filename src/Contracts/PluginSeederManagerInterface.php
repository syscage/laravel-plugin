<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Seeds every enabled plugin using its own "Database\Seeders\DatabaseSeeder",
 * so `php artisan db:seed` covers plugins the way `php artisan migrate` does.
 */
interface PluginSeederManagerInterface
{
    /**
     * Run each enabled plugin's DatabaseSeeder, skipping plugins that have none.
     *
     * @param  string|null  $database  The database connection to seed.
     * @return array<int, string> The aliases of the plugins that were seeded.
     */
    public function seedEnabled(?string $database = null, ?OutputInterface $output = null): array;
}
