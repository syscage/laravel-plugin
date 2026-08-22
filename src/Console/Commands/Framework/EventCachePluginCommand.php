<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesDirectly;

/**
 * Delegates to "event:cache". The "plugin" argument only validates that
 * the given alias exists; event discovery caching is always
 * application-wide.
 */
final class EventCachePluginCommand extends Command
{
    use DelegatesDirectly;

    protected $signature = 'event:cache-plugin {plugin : Validated for command-family consistency; has no effect on scope}';

    protected $description = 'Discover and cache the application\'s events and listeners (identical to "event:cache")';

    protected function delegateCommand(): string
    {
        return 'event:cache';
    }
}
