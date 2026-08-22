<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Concerns;

/**
 * Delegates to one of Laravel's own migration commands, forwarding every
 * locally defined option and forcing "--path"/"--realpath" to scope the
 * operation to the target plugin's "database/migrations" directory.
 */
trait DelegatesWithMigrationPath
{
    use ResolvesTargetPlugin;

    abstract protected function delegateCommand(): string;

    public function handle(): int
    {
        $arguments = [
            '--path' => [$this->targetPlugin()->migrationPath()],
            '--realpath' => true,
        ];

        foreach ($this->options() as $key => $value) {
            if (in_array($key, ['path', 'realpath'], true) || $value === null || $value === false) {
                continue;
            }

            $arguments['--' . $key] = $value;
        }

        return $this->call($this->delegateCommand(), $arguments);
    }
}
