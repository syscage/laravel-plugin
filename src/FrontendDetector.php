<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Illuminate\Filesystem\Filesystem;
use Syscage\Plugin\Contracts\FrontendDetectorInterface;

/**
 * Default implementation of {@see FrontendDetectorInterface}.
 *
 * When `config('plugin.frontend')` is set to anything other than "auto",
 * that value is honored as-is. Otherwise, the host's "package.json" is
 * inspected for the presence of React/Vue and Inertia packages.
 */
final class FrontendDetector implements FrontendDetectorInterface
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $packageJsonPath,
        private readonly string $configured,
    ) {
    }

    public function detect(): string
    {
        if ($this->configured !== 'auto') {
            return $this->configured;
        }

        if (! $this->files->isFile($this->packageJsonPath)) {
            return 'blade';
        }

        $decoded = json_decode($this->files->get($this->packageJsonPath), true);
        $packages = array_keys(array_merge(
            (array) ($decoded['dependencies'] ?? []),
            (array) ($decoded['devDependencies'] ?? []),
        ));

        if (in_array('@inertiajs/react', $packages, true)) {
            return 'inertia-react';
        }

        if (in_array('@inertiajs/vue3', $packages, true) || in_array('@inertiajs/vue2', $packages, true)) {
            return 'inertia-vue';
        }

        if (in_array('react', $packages, true)) {
            return 'react';
        }

        if (in_array('vue', $packages, true)) {
            return 'vue';
        }

        return 'blade';
    }
}
