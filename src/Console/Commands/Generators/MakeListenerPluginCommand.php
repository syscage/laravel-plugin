<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ListenerMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeListenerPluginCommand extends ListenerMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:listener-plugin';

    protected $description = 'Create a new event listener class inside a plugin';
}
