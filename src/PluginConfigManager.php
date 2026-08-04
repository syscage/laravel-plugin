<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginConfigManagerInterface;
use Syscage\Plugin\Contracts\PluginInterface;

/**
 * Default implementation of {@see PluginConfigManagerInterface}.
 *
 * Mirrors the merge behaviour of
 * {@see \Illuminate\Support\ServiceProvider::mergeConfigFrom()}.
 */
final class PluginConfigManager implements PluginConfigManagerInterface
{
    public function __construct(
        private readonly Repository $config,
        private readonly Application $app,
        private readonly Filesystem $files,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        if ($this->app instanceof CachesConfiguration && $this->app->configurationIsCached()) {
            return;
        }

        $directory = $plugin->configPath();

        if (! $this->files->isDirectory($directory)) {
            return;
        }

        foreach ($this->files->files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $name = $file->getFilenameWithoutExtension();
            $key = $name === 'config' ? $plugin->alias() : $plugin->alias() . '.' . $name;

            $this->config->set($key, array_merge(
                (array) require $file->getPathname(),
                (array) $this->config->get($key, []),
            ));
        }
    }
}
