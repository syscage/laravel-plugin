<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Generators;

use Illuminate\Foundation\Console\MailMakeCommand;
use Syscage\Plugin\Console\Commands\Concerns\GeneratesIntoPluginAppPath;

final class MakeMailPluginCommand extends MailMakeCommand
{
    use GeneratesIntoPluginAppPath;

    protected $name = 'make:mail-plugin';

    protected $description = 'Create a new email class inside a plugin';
}
