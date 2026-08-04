<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\RuleMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeRulePluginCommand extends RuleMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:rule-plugin';

    protected $description = 'Create a new validation rule inside a plugin';
}
