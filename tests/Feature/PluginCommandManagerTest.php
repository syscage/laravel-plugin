<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginCommandManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginCommandManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_registers_class_based_console_commands(): void
    {
        $manager = $this->app->make(PluginCommandManager::class);

        $manager->register($this->makeResourcePlugin());

        $this->artisan('resource-plugin:demo')
            ->expectsOutputToContain('demo-command-ran')
            ->assertSuccessful();
    }
}
