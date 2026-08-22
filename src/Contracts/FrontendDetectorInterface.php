<?php

declare(strict_types=1);

namespace Syscage\Plugin\Contracts;

/**
 * Detects which frontend stack the host application uses.
 */
interface FrontendDetectorInterface
{
    /**
     * One of "blade", "react", "vue", "inertia-react", "inertia-vue".
     */
    public function detect(): string;
}
