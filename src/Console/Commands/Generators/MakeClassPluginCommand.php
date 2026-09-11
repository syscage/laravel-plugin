<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\ClassMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeClassPluginCommand extends ClassMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:class-plugin';

    protected $description = 'Create a new class inside a plugin';
}
