<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginRouteManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginRouteManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_registers_web_and_api_routes(): void
    {
        $manager = $this->app->make(PluginRouteManager::class);

        $manager->register($this->makeResourcePlugin());

        $this->get('/resource-plugin-test/web')->assertOk()->assertSee('ok-web');
        $this->get('/api/resource-plugin-test/api')->assertOk()->assertSee('ok-api');
    }

    public function test_it_registers_console_routes(): void
    {
        $manager = $this->app->make(PluginRouteManager::class);

        $manager->register($this->makeResourcePlugin());

        $this->artisan('resource-plugin:closure-demo')->assertSuccessful();
    }
}
