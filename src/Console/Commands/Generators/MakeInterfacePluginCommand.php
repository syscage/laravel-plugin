<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\InterfaceMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeInterfacePluginCommand extends InterfaceMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:interface-plugin';

    protected $description = 'Create a new interface inside a plugin';
}
