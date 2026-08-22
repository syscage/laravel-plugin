<?php

declare(strict_types=1);

namespace Syscage\Plugin;

use Composer\Semver\Semver;
use InvalidArgumentException;
use Syscage\Plugin\Contracts\PluginDependencyResolverInterface;
use Syscage\Plugin\Contracts\PluginInterface;
use Syscage\Plugin\Exceptions\CircularPluginDependencyException;
use Syscage\Plugin\Exceptions\ConflictingPluginException;
use Syscage\Plugin\Exceptions\MissingPluginDependencyException;

/**
 * Resolves plugin load order from each plugin's "requires"/"conflicts"
 * manifest entries using a priority-stable topological sort.
 */
final class PluginDependencyResolver implements PluginDependencyResolverInterface
{
    public function resolve(array $plugins): array
    {
        foreach ($plugins as $plugin) {
            $this->validate($plugin, $plugins);
        }

        return $this->order($plugins);
    }

    public function validate(PluginInterface $plugin, array $plugins): void
    {
        foreach ($plugin->requires() as $requirement) {
            $required = $this->normalize($requirement);
            $dependency = $plugins[$required['alias']] ?? null;

            if ($dependency === null || ! $dependency->enabled()) {
                throw MissingPluginDependencyException::notPresent($plugin->alias(), $required['alias']);
            }

            if ($required['version'] !== null && ! Semver::satisfies($dependency->version(), $required['version'])) {
                throw MissingPluginDependencyException::versionMismatch(
                    $plugin->alias(),
                    $required['alias'],
                    $required['version'],
                    $dependency->version(),
                );
            }
        }

        foreach ($plugin->conflicts() as $conflictingAlias) {
            $conflict = $plugins[$conflictingAlias] ?? null;

            if ($conflict !== null && $conflict->enabled()) {
                throw ConflictingPluginException::between($plugin->alias(), $conflictingAlias);
            }
        }
    }

    /**
     * @param array<string, PluginInterface> $plugins
     *
     * @return array<string, PluginInterface>
     */
    private function order(array $plugins): array
    {
        $remaining = $plugins;
        $ordered = [];

        $dependencies = [];

        foreach ($plugins as $alias => $plugin) {
            $dependencies[$alias] = array_map(
                fn (array|string $requirement): string => $this->normalize($requirement)['alias'],
                $plugin->requires(),
            );
        }

        while ($remaining !== []) {
            $ready = [];

            foreach ($remaining as $alias => $plugin) {
                $unresolved = array_diff($dependencies[$alias], array_keys($ordered));

                if ($unresolved === []) {
                    $ready[$alias] = $plugin;
                }
            }

            if ($ready === []) {
                throw CircularPluginDependencyException::forCycle(array_keys($remaining));
            }

            uasort(
                $ready,
                static fn (PluginInterface $a, PluginInterface $b): int => [$a->priority(), $a->alias()] <=> [$b->priority(), $b->alias()],
            );

            $next = array_key_first($ready);
            $ordered[$next] = $remaining[$next];
            unset($remaining[$next]);
        }

        return $ordered;
    }

    /**
     * @return array{alias: string, version: ?string}
     */
    private function normalize(array|string $requirement): array
    {
        if (is_string($requirement)) {
            return ['alias' => $requirement, 'version' => null];
        }

        $alias = $requirement['plugin'] ?? $requirement['alias'] ?? $requirement['name'] ?? null;

        if ($alias === null) {
            throw new InvalidArgumentException(
                'A plugin requirement entry must be a string alias or an array containing an "alias" key.',
            );
        }

        $version = $requirement['version'] ?? $requirement['constraint'] ?? null;

        return [
            'alias' => (string) $alias,
            'version' => $version !== null ? (string) $version : null,
        ];
    }
}
