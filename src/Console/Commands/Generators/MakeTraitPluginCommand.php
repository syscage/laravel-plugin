<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\TraitMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeTraitPluginCommand extends TraitMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:trait-plugin';

    protected $description = 'Create a new trait inside a plugin';
}
