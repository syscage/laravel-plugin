<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ObserverMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeObserverPluginCommand extends ObserverMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:observer-plugin';

    protected $description = 'Create a new observer class inside a plugin';
}
