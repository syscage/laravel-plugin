<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Syscage\Plugin\PluginTranslationManager;
use Syscage\Plugin\Tests\Support\ResourcePluginFixture;
use Syscage\Plugin\Tests\TestCase;

final class PluginTranslationManagerTest extends TestCase
{
    use ResourcePluginFixture;

    public function test_it_registers_the_plugin_translation_namespace(): void
    {
        $manager = $this->app->make(PluginTranslationManager::class);
        $plugin = $this->makeResourcePlugin();

        $manager->register($plugin);

        $this->assertSame('Welcome!', trans($plugin->alias() . '::messages.welcome'));
    }
}
