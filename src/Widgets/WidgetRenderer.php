<?php

declare(strict_types=1);

namespace Syscage\Plugin\Widgets;

use Illuminate\Support\Str;
use Syscage\Plugin\Contracts\DashboardWidgetInterface;
use Syscage\Plugin\Contracts\FrontendDetectorInterface;
use Syscage\Plugin\Contracts\WidgetRendererInterface;

/**
 * Default implementation of {@see WidgetRendererInterface}.
 *
 * For a Blade host, a widget's component reference (e.g.
 * "MyBlog/Widgets/MessagesToday") is converted into the dot-notated view
 * name "widgets.my-blog.messages-today", which the plugin (or host
 * application) is expected to register a matching view for. For every
 * other detected frontend, the component reference is returned unchanged,
 * to be resolved by the generated frontend manifest.
 */
final class WidgetRenderer implements WidgetRendererInterface
{
    public function __construct(
        private readonly FrontendDetectorInterface $frontend,
    ) {
    }

    public function resolve(DashboardWidgetInterface $widget): string
    {
        if ($this->frontend->detect() === 'blade') {
            return $this->toViewName($widget->component());
        }

        return $widget->component();
    }

    private function toViewName(string $component): string
    {
        $segments = array_filter(explode('/', str_replace('\\', '/', $component)));

        $kebabbed = array_map(static fn (string $segment): string => Str::kebab($segment), $segments);

        return 'widgets.' . implode('.', $kebabbed);
    }
}
