<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginConfigManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginConfigManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_merges_config_php_directly_under_the_plugin_alias(): void
    {
        $manager = $this->app->make(PluginConfigManager::class);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        $this->assertSame('bar', config($plugin->alias() . '.foo'));
    }

    public function test_it_merges_other_config_files_under_a_sub_key(): void
    {
        $manager = $this->app->make(PluginConfigManager::class);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        $this->assertSame(1, config($plugin->alias() . '.extra.x'));
    }
}
