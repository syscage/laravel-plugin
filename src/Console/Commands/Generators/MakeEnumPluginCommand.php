<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\EnumMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeEnumPluginCommand extends EnumMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:enum-plugin';

    protected $description = 'Create a new enum inside a plugin';
}
