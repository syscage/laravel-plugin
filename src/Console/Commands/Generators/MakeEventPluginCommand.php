<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\EventMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeEventPluginCommand extends EventMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:event-plugin';

    protected $description = 'Create a new event class inside a plugin';
}
