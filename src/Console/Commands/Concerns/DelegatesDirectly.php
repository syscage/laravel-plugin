<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

/**
 * Validates an (otherwise unused) target plugin argument, then delegates
 * straight through to an underlying Laravel command that has no
 * per-plugin scoping concept of its own (e.g. compiled framework caches,
 * which are always built for the whole application).
 */
trait DelegatesDirectly
{
    use ResolvesTargetPlugin;

    abstract protected function delegateCommand(): string;

    public function handle(): int
    {
        $this->targetPlugin();

        return $this->call($this->delegateCommand());
    }
}
