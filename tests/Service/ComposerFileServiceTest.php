<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Service;

use PHPUnit\Framework\TestCase;
use Vildanbina\ComposerUpgrader\Service\ComposerFileService;

class ComposerFileServiceTest extends TestCase
{
    private ComposerFileService $service;

    private string $tempFile;

    protected function setUp(): void
    {
        $this->service = new ComposerFileService();
        $this->tempFile = sys_get_temp_dir().'/composer_test.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function test_load_composer_json(): void
    {
        $data = ['require' => ['package/a' => '^1.0']];
        file_put_contents($this->tempFile, json_encode($data));
        $result = $this->service->loadComposerJson($this->tempFile);
        $this->assertEquals($data, $result);

        $this->assertNull($this->service->loadComposerJson('/nonexistent/file.json'));
    }

    public function test_get_dependencies(): void
    {
        $composerJson = [
            'require' => ['package/a' => '^1.0'],
            'require-dev' => ['package/b' => '^2.0'],
        ];
        $expected = [
            'package/a' => '^1.0',
            'package/b' => '^2.0',
        ];
        $this->assertEquals($expected, $this->service->getDependencies($composerJson));
    }

    public function test_update_dependency(): void
    {
        $composerJson = [
            'require' => ['package/a' => '^1.0'],
            'require-dev' => ['package/b' => '^2.0'],
        ];
        $this->service->updateDependency($composerJson, 'package/a', '^1.1');
        $this->assertEquals('^1.1', $composerJson['require']['package/a']);
        $this->service->updateDependency($composerJson, 'package/b', '^2.1');
        $this->assertEquals('^2.1', $composerJson['require-dev']['package/b']);
    }

    public function test_save_composer_json(): void
    {
        $data = ['require' => ['package/a' => '^1.0']];
        $this->service->saveComposerJson($data, $this->tempFile);
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), file_get_contents($this->tempFile));
    }

    public function test_load_empty_composer_json(): void
    {
        file_put_contents($this->tempFile, json_encode([]));
        $result = $this->service->loadComposerJson($this->tempFile);
        $this->assertEquals([], $result);
    }

    public function test_load_invalid_json(): void
    {
        file_put_contents($this->tempFile, '{ invalid json }');
        $result = $this->service->loadComposerJson($this->tempFile);
        $this->assertNull($result);
    }
}
