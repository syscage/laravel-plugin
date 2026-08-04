<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\PolicyMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakePolicyPluginCommand extends PolicyMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:policy-plugin';

    protected $description = 'Create a new policy class inside a plugin';
}
