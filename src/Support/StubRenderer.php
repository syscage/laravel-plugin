<?php

declare(strict_types=1);

namespace Syscage\Plugin\Support;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

/**
 * Renders a stub template from the configured stubs directory, replacing
 * "{{ token }}" placeholders with the given values.
 */
final class StubRenderer
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $stubsPath,
    ) {
    }

    /**
     * @param array<string, string> $replacements Keyed by token name, without the surrounding "{{ }}".
     */
    public function render(string $stub, array $replacements): string
    {
        $path = rtrim($this->stubsPath, '/\\') . DIRECTORY_SEPARATOR . $stub;

        if (! $this->files->isFile($path)) {
            throw new RuntimeException("No stub found at [{$path}].");
        }

        $search = [];
        $replace = [];

        foreach ($replacements as $token => $value) {
            $search[] = '{{ ' . $token . ' }}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $this->files->get($path));
    }
}
