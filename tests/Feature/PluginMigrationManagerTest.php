<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Database\Migrations\Migrator;
use Syscage\Plugin\PluginMigrationManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginMigrationManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_registers_the_plugin_migration_path(): void
    {
        $manager = $this->app->make(PluginMigrationManager::class);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        /** @var Migrator $migrator */
        $migrator = $this->app->make('migrator');

        $this->assertContains($plugin->migrationPath(), $migrator->paths());
    }
}
