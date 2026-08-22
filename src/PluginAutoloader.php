<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Composer\Autoload\ClassLoader;
use RuntimeException;
use Syscage\Plugin\Contracts\PluginAutoloaderInterface;

/**
 * Registers plugin namespaces directly on Composer's {@see ClassLoader},
 * discovered via the currently registered SPL autoload stack. This lets
 * newly generated plugins be autoloaded immediately, without requiring a
 * "composer dump-autoload" after every "make:plugin" run.
 */
final class PluginAutoloader implements PluginAutoloaderInterface
{
    /**
     * Tracks each registered "namespace|path" pair. A namespace may map to
     * more than one directory — e.g. a plugin's root namespace resolves
     * against both its "src/" and "src/app/" directories — so registration
     * is deduplicated per pair, not per namespace.
     *
     * @var array<string, true>
     */
    private array $registered = [];

    private ?ClassLoader $loader = null;

    public function register(string $namespace, string $path): void
    {
        $namespace = rtrim($namespace, '\\') . '\\';
        $key = $namespace . '|' . $path;

        if (isset($this->registered[$key])) {
            return;
        }

        $this->resolveClassLoader()->addPsr4($namespace, $path);

        $this->registered[$key] = true;
    }

    public function isRegistered(string $namespace): bool
    {
        $prefix = rtrim($namespace, '\\') . '\\|';

        foreach (array_keys($this->registered) as $key) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolveClassLoader(): ClassLoader
    {
        if ($this->loader !== null) {
            return $this->loader;
        }

        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && $function[0] instanceof ClassLoader) {
                return $this->loader = $function[0];
            }
        }

        throw new RuntimeException(
            'Unable to locate the Composer class loader. The plugin autoloader requires '
            . 'Composer\'s "vendor/autoload.php" to have already been loaded.',
        );
    }
}
