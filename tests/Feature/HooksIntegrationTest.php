<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Feature;

use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Plugin\Contracts\HookManagerInterface;
use Syscage\Plugin\Events\PluginDisabled;
use Syscage\Plugin\Events\PluginUninstalled;
use Syscage\Plugin\Facades\Hooks;
use Syscage\Plugin\Tests\Support\FakePlugin;
use Syscage\Plugin\Tests\TestCase;

final class HooksIntegrationTest extends TestCase
{
    public function test_the_facade_resolves_the_shared_singleton(): void
    {
        Hooks::modify('x', static fn (int $v): int => $v + 1, plugin: 'blog');

        $this->assertSame(2, $this->app->make(HookManagerInterface::class)->filter('x', 1));
    }

    public function test_disabling_or_uninstalling_a_plugin_forgets_its_hooks(): void
    {
        $events = $this->app->make(Dispatcher::class);

        Hooks::on('a', static fn () => null, plugin: 'blog');
        $events->dispatch(new PluginDisabled(new FakePlugin('blog')));
        $this->assertSame([], Hooks::all());

        Hooks::on('a', static fn () => null, plugin: 'blog');
        $events->dispatch(new PluginUninstalled(new FakePlugin('blog')));
        $this->assertSame([], Hooks::all());
    }

    public function test_hooks_list_prints_registrations(): void
    {
        $this->artisan('hooks:list')->expectsOutputToContain('No hooks registered.')->assertSuccessful();

        Hooks::modify('dashboard.widgets', static fn (array $w): array => $w, plugin: 'blog');

        $this->artisan('hooks:list')->expectsOutputToContain('dashboard.widgets')->assertSuccessful();
    }
}
