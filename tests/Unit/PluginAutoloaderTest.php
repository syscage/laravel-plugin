<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Syscage\Plugin\PluginAutoloader;

final class PluginAutoloaderTest extends TestCase
{
    public function test_it_registers_a_namespace_and_makes_its_classes_loadable(): void
    {
        $autoloader = new PluginAutoloader();

        $this->assertFalse($autoloader->isRegistered('Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin'));

        $autoloader->register(
            'Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin',
            __DIR__ . '/../Fixtures/plugins/demo-plugin/src',
        );

        $this->assertTrue($autoloader->isRegistered('Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin'));
        $this->assertTrue(class_exists(\Syscage\Plugin\Tests\Fixtures\DemoPlugin\Plugin::class));
    }

    public function test_registering_the_same_namespace_twice_is_a_no_op(): void
    {
        $autoloader = new PluginAutoloader();

        $autoloader->register('Syscage\\Plugin\\Tests\\Fixtures\\Idempotent', __DIR__);
        $autoloader->register('Syscage\\Plugin\\Tests\\Fixtures\\Idempotent', __DIR__);

        $this->assertTrue($autoloader->isRegistered('Syscage\\Plugin\\Tests\\Fixtures\\Idempotent'));
    }

    public function test_the_same_namespace_can_map_to_multiple_directories(): void
    {
        $autoloader = new PluginAutoloader();
        $namespace = 'Syscage\\Plugin\\Tests\\Fixtures\\DemoPlugin';

        $autoloader->register($namespace, __DIR__ . '/../Fixtures/plugins/demo-plugin/src');
        $autoloader->register($namespace, __DIR__ . '/../Fixtures/plugins/demo-plugin/src/app');

        $this->assertTrue(class_exists(\Syscage\Plugin\Tests\Fixtures\DemoPlugin\Plugin::class));
        $this->assertTrue(class_exists(\Syscage\Plugin\Tests\Fixtures\DemoPlugin\Providers\DemoPluginServiceProvider::class));
    }
}
