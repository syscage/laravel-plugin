<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeControllerPluginCommand extends ControllerMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:controller-plugin';

    protected $description = 'Create a new controller class inside a plugin';
}
