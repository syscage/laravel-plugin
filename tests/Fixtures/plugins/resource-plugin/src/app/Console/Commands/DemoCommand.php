<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Fixtures\ResourcePlugin\Console\Commands;

use Illuminate\Console\Command;

final class DemoCommand extends Command
{
    protected $signature = 'resource-plugin:demo';

    protected $description = 'A class-based console command contributed by the resource plugin fixture.';

    public function handle(): int
    {
        $this->line('demo-command-ran');

        return self::SUCCESS;
    }
}
