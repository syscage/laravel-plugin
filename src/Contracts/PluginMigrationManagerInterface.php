<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Registers a plugin's "database/migrations" directory with Laravel's
 * migrator, so `php artisan migrate` picks them up automatically.
 */
interface PluginMigrationManagerInterface
{
    public function register(PluginInterface $plugin): void;
}
