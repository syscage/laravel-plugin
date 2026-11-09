<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use LogicException;
use Syscage\Plugin\Contracts\HookManagerInterface;

/**
 * In-memory implementation of {@see HookManagerInterface}. Hooks are
 * re-registered by plugin providers on every boot, so nothing is cached.
 */
final class HookManager implements HookManagerInterface
{
    /** @var array<string, array{callback: callable, plugin: ?string}> */
    private array $providers = [];

    /** @var array<string, array<int, array<int, array{callback: callable, plugin: ?string}>>> */
    private array $filters = [];

    /** @var array<string, array<int, array<int, array{callback: callable, plugin: ?string}>>> */
    private array $actions = [];

    public function provide(string $name, callable $callback, ?string $plugin = null): void
    {
        if (isset($this->providers[$name])) {
            $owner = $this->providers[$name]['plugin'] ?? 'core';

            throw new LogicException("Hook [{$name}] is already provided by [{$owner}].");
        }

        $this->providers[$name] = ['callback' => $callback, 'plugin' => $plugin];
    }

    public function has(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    public function call(string $name, mixed ...$args): mixed
    {
        return $this->callOr($name, null, ...$args);
    }

    public function callOr(string $name, mixed $default, mixed ...$args): mixed
    {
        return isset($this->providers[$name])
            ? ($this->providers[$name]['callback'])(...$args)
            : $default;
    }

    public function modify(string $name, callable $callback, int $priority = 10, ?string $plugin = null): void
    {
        $this->filters[$name][$priority][] = ['callback' => $callback, 'plugin' => $plugin];
    }

    public function filter(string $name, mixed $value, mixed ...$args): mixed
    {
        foreach ($this->ordered($this->filters[$name] ?? []) as $listener) {
            $value = ($listener['callback'])($value, ...$args);
        }

        return $value;
    }

    public function on(string $name, callable $callback, int $priority = 10, ?string $plugin = null): void
    {
        $this->actions[$name][$priority][] = ['callback' => $callback, 'plugin' => $plugin];
    }

    public function do(string $name, mixed ...$args): void
    {
        foreach ($this->ordered($this->actions[$name] ?? []) as $listener) {
            ($listener['callback'])(...$args);
        }
    }

    public function forget(string $plugin): void
    {
        $this->providers = array_filter($this->providers, static fn (array $p): bool => $p['plugin'] !== $plugin);
        $this->filters = $this->withoutPlugin($this->filters, $plugin);
        $this->actions = $this->withoutPlugin($this->actions, $plugin);
    }

    public function all(): array
    {
        $rows = [];

        foreach ($this->providers as $name => $provider) {
            $rows[] = ['type' => 'provide', 'name' => $name, 'priority' => null, 'plugin' => $provider['plugin'] ?? 'core'];
        }

        foreach (['filter' => $this->filters, 'action' => $this->actions] as $type => $group) {
            foreach ($group as $name => $priorities) {
                foreach ($priorities as $priority => $listeners) {
                    foreach ($listeners as $listener) {
                        $rows[] = ['type' => $type, 'name' => $name, 'priority' => $priority, 'plugin' => $listener['plugin'] ?? 'core'];
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * @param array<string, array<int, array<int, array{callback: callable, plugin: ?string}>>> $group
     *
     * @return array<string, array<int, array<int, array{callback: callable, plugin: ?string}>>>
     */
    private function withoutPlugin(array $group, string $plugin): array
    {
        $result = [];

        foreach ($group as $name => $priorities) {
            foreach ($priorities as $priority => $listeners) {
                $kept = array_values(array_filter($listeners, static fn (array $l): bool => $l['plugin'] !== $plugin));

                if ($kept !== []) {
                    $result[$name][$priority] = $kept;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<int, array<int, array{callback: callable, plugin: ?string}>> $priorities
     *
     * @return array<int, array{callback: callable, plugin: ?string}>
     */
    private function ordered(array $priorities): array
    {
        ksort($priorities);

        return array_merge([], ...array_values($priorities));
    }
}
