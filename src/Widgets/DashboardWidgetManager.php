<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\Dispatcher;
use Syscage\Plugin\Contracts\DashboardWidgetAuthorizationInterface;
use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Contracts\DashboardWidgetManagerInterface;
use Syscage\Plugin\Contracts\DashboardWidgetRegistryInterface;
use Syscage\Plugin\Events\WidgetRegistered;
use Syscage\Plugin\Events\WidgetRemoved;

/**
 * Default implementation of {@see DashboardWidgetManagerInterface}.
 */
final class DashboardWidgetManager implements DashboardWidgetManagerInterface
{
    public function __construct(
        private readonly DashboardWidgetRegistryInterface $registry,
        private readonly DashboardWidgetAuthorizationInterface $authorization,
        private readonly Dispatcher $events,
    ) {
    }

    public function all(): array
    {
        return $this->registry->all();
    }

    public function find(string $id): DashboardWidgetInterface
    {
        return $this->registry->find($id);
    }

    public function has(string $id): bool
    {
        return $this->registry->has($id);
    }

    public function register(DashboardWidgetInterface $widget): void
    {
        $this->registry->register($widget);

        $this->events->dispatch(new WidgetRegistered($widget));
    }

    public function forget(string $id): void
    {
        $widget = $this->registry->find($id);

        $this->registry->forget($id);

        $this->events->dispatch(new WidgetRemoved($widget));
    }

    public function enabled(): array
    {
        return $this->registry->enabled();
    }

    public function availableFor(Authenticatable $user): array
    {
        return array_filter(
            $this->enabled(),
            fn (DashboardWidgetInterface $widget): bool => $this->authorization->authorize($widget, $user),
        );
    }
}
