<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

use Illuminate\Support\Str;

/**
 * Redirects a Laravel generator command that normally writes into the host
 * application's "app/" directory (models, controllers, middleware,
 * requests, providers, policies, observers, events, listeners, mail,
 * notifications, jobs, rules, resources, casts, enums) into the target
 * plugin's "src/app/" directory instead, under the plugin's own namespace.
 */
trait GeneratesIntoPluginAppPath
{
    use ResolvesTargetPlugin;

    protected function rootNamespace()
    {
        return rtrim($this->targetPlugin()->namespace(), '\\') . '\\';
    }

    protected function getPath($name)
    {
        $relative = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->targetPlugin()->appPath(str_replace('\\', '/', $relative) . '.php');
    }
}
