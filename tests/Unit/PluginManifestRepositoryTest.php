<?php

declare(strict_types=1);

namespace Syscage\Plugin\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Syscage\Plugin\Exceptions\InvalidPluginManifestException;
use Syscage\Plugin\PluginManifestRepository;
use Syscage\Plugin\Support\PluginManifest;

final class PluginManifestRepositoryTest extends TestCase
{
    private string $tempDir;

    private PluginManifestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('plugin-manifest-', true);
        mkdir($this->tempDir, recursive: true);

        $this->repository = new PluginManifestRepository(new Filesystem());
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tempDir . DIRECTORY_SEPARATOR . '*') ?: []);
        rmdir($this->tempDir);

        parent::tearDown();
    }

    private function manifestPath(): string
    {
        return $this->tempDir . DIRECTORY_SEPARATOR . 'plugin.json';
    }

    public function test_exists_returns_false_when_no_manifest_is_present(): void
    {
        $this->assertFalse($this->repository->exists($this->manifestPath()));
    }

    public function test_it_reads_a_valid_manifest(): void
    {
        file_put_contents($this->manifestPath(), json_encode([
            'name' => 'MyPlugin',
            'alias' => 'my-plugin',
            'version' => '1.0.0',
            'provider' => 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider',
            'namespace' => 'Plugins\\MyPlugin',
        ]));

        $manifest = $this->repository->read($this->manifestPath());

        $this->assertSame('MyPlugin', $manifest->name());
        $this->assertSame('my-plugin', $manifest->alias());
    }

    public function test_it_throws_for_malformed_json(): void
    {
        file_put_contents($this->manifestPath(), '{not valid json');

        $this->expectException(InvalidPluginManifestException::class);

        $this->repository->read($this->manifestPath());
    }

    public function test_it_writes_a_manifest_back_to_disk(): void
    {
        $manifest = PluginManifest::fromArray([
            'name' => 'MyPlugin',
            'alias' => 'my-plugin',
            'version' => '1.0.0',
            'provider' => 'Plugins\\MyPlugin\\Providers\\MyPluginServiceProvider',
            'namespace' => 'Plugins\\MyPlugin',
        ])->withId('generated-uuid');

        $this->repository->write($this->manifestPath(), $manifest);

        $roundTripped = $this->repository->read($this->manifestPath());

        $this->assertSame('generated-uuid', $roundTripped->id());
    }
}
