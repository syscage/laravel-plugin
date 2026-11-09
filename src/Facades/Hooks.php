<?php

declare(strict_types=1);

namespace Syscage\Plugin\Facades;

use Illuminate\Support\Facades\Facade;
use Syscage\Plugin\Contracts\HookManagerInterface;

/**
 * @method static void provide(string $name, callable $callback, ?string $plugin = null)
 * @method static bool has(string $name)
 * @method static mixed call(string $name, mixed ...$args)
 * @method static mixed callOr(string $name, mixed $default, mixed ...$args)
 * @method static void modify(string $name, callable $callback, int $priority = 10, ?string $plugin = null)
 * @method static mixed filter(string $name, mixed $value, mixed ...$args)
 * @method static void on(string $name, callable $callback, int $priority = 10, ?string $plugin = null)
 * @method static void do(string $name, mixed ...$args)
 * @method static void forget(string $plugin)
 * @method static array all()
 *
 * @see HookManagerInterface
 */
final class Hooks extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HookManagerInterface::class;
    }
}
