<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesDirectly;

/**
 * Delegates to "route:cache". Plugin routes are registered automatically
 * during normal boot, so caching routes always includes every enabled
 * plugin's contribution; the "plugin" argument only validates that the
 * given alias exists.
 */
final class RouteCachePluginCommand extends Command
{
    use DelegatesDirectly;

    protected $signature = 'route:cache-plugin {plugin : Validated for command-family consistency; has no effect on scope}';

    protected $description = 'Create a route cache file for faster route registration (identical to "route:cache")';

    protected function delegateCommand(): string
    {
        return 'route:cache';
    }
}
