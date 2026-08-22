<?php

declare(strict_types=1);

namespace Syscage\Plugin\Console\Commands\Queue;

use Illuminate\Console\Command;
use Syscage\Plugin\Console\Commands\Concerns\ResolvesTargetPlugin;

/**
 * Delegates to "queue:work". When "--queue" is not given, it defaults to
 * the plugin's own alias, following the convention that a plugin's jobs
 * are pushed onto a queue named after it.
 */
final class QueueWorkPluginCommand extends Command
{
    use ResolvesTargetPlugin;

    protected $signature = 'queue:work-plugin
        {plugin : The plugin whose queue should be worked}
        {--name=default : The name of the worker}
        {--queue= : The names of the queues to work; defaults to the plugin\'s alias}
        {--once : Only process the next job on the queue}
        {--stop-when-empty : Stop when the queue is empty}
        {--max-jobs=0 : The number of jobs to process before stopping}
        {--max-time=0 : The maximum number of seconds the worker should run}
        {--force : Force the worker to run even in maintenance mode}
        {--memory=128 : The memory limit in megabytes}
        {--sleep=3 : The number of seconds to sleep when no job is available}
        {--rest=0 : The number of seconds to rest between jobs}
        {--timeout=60 : The number of seconds a child process can run}
        {--tries=1 : The number of times to attempt a job before logging it failed}';

    protected $description = "Process a plugin's queued jobs";

    public function handle(): int
    {
        $plugin = $this->targetPlugin();

        return $this->call('queue:work', array_filter([
            '--name' => $this->option('name'),
            '--queue' => $this->option('queue') ?? $plugin->alias(),
            '--once' => $this->option('once'),
            '--stop-when-empty' => $this->option('stop-when-empty'),
            '--max-jobs' => $this->option('max-jobs'),
            '--max-time' => $this->option('max-time'),
            '--force' => $this->option('force'),
            '--memory' => $this->option('memory'),
            '--sleep' => $this->option('sleep'),
            '--rest' => $this->option('rest'),
            '--timeout' => $this->option('timeout'),
            '--tries' => $this->option('tries'),
        ], static fn (mixed $value): bool => $value !== null && $value !== false));
    }
}
