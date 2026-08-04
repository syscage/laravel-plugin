<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\CastMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeCastPluginCommand extends CastMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:cast-plugin';

    protected $description = 'Create a new custom Eloquent cast class inside a plugin';
}
