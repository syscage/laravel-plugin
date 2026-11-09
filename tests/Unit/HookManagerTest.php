<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\HookManager;

final class HookManagerTest extends TestCase
{
    public function test_provide_and_call(): void
    {
        $hooks = new HookManager();
        $hooks->provide('sum', static fn (int $a, int $b): int => $a + $b);

        $this->assertTrue($hooks->has('sum'));
        $this->assertSame(5, $hooks->call('sum', 2, 3));
    }

    public function test_call_returns_null_and_call_or_returns_the_default_when_unprovided(): void
    {
        $hooks = new HookManager();

        $this->assertNull($hooks->call('missing'));
        $this->assertSame('x', $hooks->callOr('missing', 'x'));
    }

    public function test_providing_a_name_twice_throws(): void
    {
        $hooks = new HookManager();
        $hooks->provide('a', static fn () => 1, 'blog');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('already provided by [blog]');

        $hooks->provide('a', static fn () => 2);
    }

    public function test_filters_run_by_ascending_priority(): void
    {
        $hooks = new HookManager();
        $hooks->modify('v', static fn (string $v): string => $v . 'B', 20);
        $hooks->modify('v', static fn (string $v): string => $v . 'A', 5);

        $this->assertSame('AB', $hooks->filter('v', ''));
        $this->assertSame('same', $hooks->filter('none', 'same'));
    }

    public function test_actions_run_by_ascending_priority_with_arguments(): void
    {
        $hooks = new HookManager();
        $log = [];
        $hooks->on('e', function (string $x) use (&$log): void {
            $log[] = "late:$x";
        }, 20);
        $hooks->on('e', function (string $x) use (&$log): void {
            $log[] = "early:$x";
        }, 1);

        $hooks->do('e', 'z');

        $this->assertSame(['early:z', 'late:z'], $log);
    }

    public function test_forget_removes_only_that_plugins_registrations(): void
    {
        $hooks = new HookManager();
        $hooks->provide('p', static fn () => 1, 'blog');
        $hooks->modify('f', static fn (int $v): int => $v + 1, 10, 'blog');
        $hooks->modify('f', static fn (int $v): int => $v + 10, 10, 'shop');
        $hooks->on('a', static fn () => null);

        $hooks->forget('blog');

        $this->assertFalse($hooks->has('p'));
        $this->assertSame(10, $hooks->filter('f', 0));
        $this->assertSame(
            [
                ['type' => 'filter', 'name' => 'f', 'priority' => 10, 'plugin' => 'shop'],
                ['type' => 'action', 'name' => 'a', 'priority' => 10, 'plugin' => 'core'],
            ],
            $hooks->all(),
        );
    }
}
