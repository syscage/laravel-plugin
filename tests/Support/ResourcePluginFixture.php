<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\PluginAutoloader;
use Syscage\Plugin\PluginManifestRepository;
use Syscage\Plugin\Tests\Fixtures\ResourcePlugin\Plugin as ResourcePlugin;

/**
 * Builds a {@see PluginInterface} instance backed by the "resource-plugin"
 * fixture, used by the resource manager feature tests.
 */
trait ResourcePluginFixture
{
    protected function resourcePluginPath(): string
    {
        return realpath(__DIR__ . '/../Fixtures/plugins/resource-plugin');
    }

    protected function makeResourcePlugin(): PluginInterface
    {
        $path = $this->resourcePluginPath();

        (new PluginAutoloader())->register(
            'Syscage\\Plugin\\Tests\\Fixtures\\ResourcePlugin',
            $path . '/src',
        );
        (new PluginAutoloader())->register(
            'Syscage\\Plugin\\Tests\\Fixtures\\ResourcePlugin',
            $path . '/src/app',
        );

        $manifest = (new PluginManifestRepository(new Filesystem()))->read($path . '/plugin.json');

        return new ResourcePlugin($manifest, $path);
    }
}
