<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginViewManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginViewManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_registers_the_plugin_view_namespace(): void
    {
        $manager = $this->app->make(PluginViewManager::class);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        $this->assertSame(
            "Hello World\n",
            view($plugin->alias() . '::hello', ['name' => 'World'])->render(),
        );
    }
}
