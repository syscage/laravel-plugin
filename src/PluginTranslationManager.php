<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\Translator;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Contracts\PluginTranslationManagerInterface;

/**
 * Default implementation of {@see PluginTranslationManagerInterface}.
 */
final class PluginTranslationManager implements PluginTranslationManagerInterface
{
    public function __construct(
        private readonly Translator $translator,
        private readonly Filesystem $files,
    ) {
    }

    public function register(PluginInterface $plugin): void
    {
        $path = $plugin->langPath();

        if ($this->files->isDirectory($path)) {
            $this->translator->addNamespace($plugin->alias(), $path);
        }
    }
}
