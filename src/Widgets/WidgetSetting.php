<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Syscage\Plugin\Contracts\WidgetConfigurationInterface;

/**
 * Default, immutable implementation of {@see WidgetConfigurationInterface}.
 */
final class WidgetSetting implements WidgetConfigurationInterface
{
    /**
     * @param array<int, mixed> $options
     */
    public function __construct(
        private readonly string $key,
        private readonly string $type,
        private readonly mixed $default = null,
        private readonly array $options = [],
    ) {
    }

    /**
     * @param array<int, mixed> $options
     */
    public static function make(string $key, string $type, mixed $default = null, array $options = []): self
    {
        return new self($key, $type, $default, $options);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'default' => $this->default,
            'options' => $this->options,
        ];
    }
}
