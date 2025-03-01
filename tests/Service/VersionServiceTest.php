<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Service;

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vildanbina\ComposerUpgrader\Service\StabilityChecker;
use Vildanbina\ComposerUpgrader\Service\VersionService;

class VersionServiceTest extends TestCase
{
    private VersionService $service;

    private Composer $composer;

    private ArrayRepository $repository;

    protected function setUp(): void
    {
        $this->service = new VersionService(new StabilityChecker());
        $this->composer = $this->createMock(Composer::class);
        $this->repository = new ArrayRepository();

        $repoManager = $this->createMock(RepositoryManager::class);
        $repoManager->method('findPackages')->willReturnCallback(fn ($name, $constraint) => $this->repository->findPackages($name, $constraint));
        $this->composer->method('getRepositoryManager')->willReturn($repoManager);

        $locker = $this->createMock(Locker::class);
        $locker->method('isLocked')->willReturn(false);
        $this->composer->method('getLocker')->willReturn($locker);

        $this->service->setComposer($this->composer);
        $this->service->setIO(new NullIO());
    }

    public function test_get_latest_version_with_patch(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '1.0.1.0', '1.0.1'));
        $this->repository->addPackage(new Package('test/package', '1.1.0.0', '1.1.0'));

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.0', false, false, true);
        $this->assertEquals('1.0.1', $latest);
    }

    public function test_get_latest_version_with_minor(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '1.1.0.0', '1.1.0'));
        $this->repository->addPackage(new Package('test/package', '2.0.0.0', '2.0.0'));

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.0', false, true, false);
        $this->assertEquals('1.1.0', $latest);
    }

    public function test_get_latest_version_with_major(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '2.0.0.0', '2.0.0'));

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.0', true, false, false);
        $this->assertEquals('2.0.0', $latest);
    }

    public function test_no_composer_set(): void
    {
        $service = new VersionService(new StabilityChecker());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer instance not set.');
        $service->getLatestVersion('test/package', 'stable', '^1.0.0', true, true, true);
    }
}
