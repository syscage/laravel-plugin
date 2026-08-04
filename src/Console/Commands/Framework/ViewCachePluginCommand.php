<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesDirectly;

/**
 * Delegates to "view:cache". Plugin view namespaces are registered
 * automatically during normal boot, so compiling views always includes
 * every enabled plugin's contribution; the "plugin" argument only
 * validates that the given alias exists.
 */
final class ViewCachePluginCommand extends Command
{
    use DelegatesDirectly;

    protected $signature = 'view:cache-plugin {plugin : Validated for command-family consistency; has no effect on scope}';

    protected $description = 'Compile all of the application\'s Blade templates (identical to "view:cache")';

    protected function delegateCommand(): string
    {
        return 'view:cache';
    }
}
