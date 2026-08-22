<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginViewManagerInterface;

/**
 * Default implementation of {@see PluginViewManagerInterface}.
 */
final class PluginViewManager implements PluginViewManagerInterface
{
    public function __construct(
        private readonly Factory $view,
        private readonly Filesystem $files,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        $path = $plugin->viewPath();

        if ($this->files->isDirectory($path)) {
            $this->view->addNamespace($plugin->alias(), $path);
        }
    }
}
