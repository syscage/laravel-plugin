<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

use LogicException;

/**
 * The single place where the host application and plugins share behaviour
 * without importing each other.
 *
 * - provide / call: a named method owned by one party, callable by anyone.
 * - modify / filter: many parties change a value in turn.
 * - on / do: many parties react to something that happened.
 *
 * Everything registered with a plugin alias is removed by {@see forget()}
 * when that plugin is disabled or uninstalled.
 */
interface HookManagerInterface
{
    /**
     * Register the single provider of a named method.
     *
     * @throws LogicException when the name is already provided
     */
    public function provide(string $name, callable $callback, ?string $plugin = null): void;

    /**
     * Determine whether a named method has a provider.
     */
    public function has(string $name): bool;

    /**
     * Call a provided method. Returns null when nobody provides the name.
     */
    public function call(string $name, mixed ...$args): mixed;

    /**
     * Call a provided method, or return the default when nobody provides it.
     */
    public function callOr(string $name, mixed $default, mixed ...$args): mixed;

    /**
     * Register a filter listener. Lower priority runs first.
     */
    public function modify(string $name, callable $callback, int $priority = 10, ?string $plugin = null): void;

    /**
     * Pass a value through every filter listener of the given name.
     */
    public function filter(string $name, mixed $value, mixed ...$args): mixed;

    /**
     * Register an action listener. Lower priority runs first.
     */
    public function on(string $name, callable $callback, int $priority = 10, ?string $plugin = null): void;

    /**
     * Run every action listener of the given name.
     */
    public function do(string $name, mixed ...$args): void;

    /**
     * Remove everything the given plugin registered.
     */
    public function forget(string $plugin): void;

    /**
     * Every registration; the owner is "core" when no plugin was given.
     *
     * @return array<int, array{type: string, name: string, priority: ?int, plugin: string}>
     */
    public function all(): array;
}
