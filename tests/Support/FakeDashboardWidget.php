<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Support;

use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Widgets\DashboardWidgetType;

/**
 * A minimal, hand-rolled {@see DashboardWidgetInterface} implementation for
 * tests that need fine-grained control over widget metadata.
 */
final class FakeDashboardWidget implements DashboardWidgetInterface
{
    /**
     * @param array<int, string> $rolesValue
     */
    public function __construct(
        private readonly string $idValue = 'demo.widget',
        private readonly string $titleValue = 'Demo Widget',
        private readonly ?string $permissionValue = null,
        private readonly array $rolesValue = [],
        private readonly DashboardWidgetType $typeValue = DashboardWidgetType::Custom,
        private readonly int $defaultWidthValue = 4,
        private readonly int $minWidthValue = 2,
        private readonly int $maxWidthValue = 12,
    ) {
    }

    public function id(): string
    {
        return $this->idValue;
    }

    public function title(): string
    {
        return $this->titleValue;
    }

    public function description(): ?string
    {
        return null;
    }

    public function component(): string
    {
        return 'Demo/Widgets/Demo';
    }

    public function permission(): ?string
    {
        return $this->permissionValue;
    }

    public function type(): DashboardWidgetType
    {
        return $this->typeValue;
    }

    public function icon(): ?string
    {
        return null;
    }

    public function roles(): array
    {
        return $this->rolesValue;
    }

    public function defaultWidth(): int
    {
        return $this->defaultWidthValue;
    }

    public function minWidth(): int
    {
        return $this->minWidthValue;
    }

    public function maxWidth(): int
    {
        return $this->maxWidthValue;
    }

    public function refreshInterval(): ?int
    {
        return null;
    }

    public function settings(): array
    {
        return [];
    }

    public function data(): array
    {
        return [];
    }
}
