<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Framework;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\DelegatesDirectly;

/**
 * Delegates to "optimize". Framework-wide optimization always includes
 * every enabled plugin's contributions (config, routes, views); the
 * "plugin" argument only validates that the given alias exists.
 */
final class OptimizePluginCommand extends Command
{
    use DelegatesDirectly;

    protected $signature = 'optimize-plugin {plugin : Validated for command-family consistency; has no effect on scope}';

    protected $description = 'Cache framework bootstrap files (identical to "optimize")';

    protected function delegateCommand(): string
    {
        return 'optimize';
    }
}
