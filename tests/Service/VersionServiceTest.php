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
use UnexpectedValueException;
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

    public function test_get_latest_version_no_valid_upgrade(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '1.0.1.0', '1.0.1'));

        // Current version is already latest patch
        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.1', false, false, true);
        $this->assertNull($latest);
    }

    public function test_get_latest_version_with_v_prefix(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '1.0.1.0', 'v1.0.1')); // v prefix

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.0', false, false, true);
        $this->assertEquals('v1.0.1', $latest);
    }

    public function test_get_latest_version_downgrade_prevented(): void
    {
        $this->repository->addPackage(new Package('test/package', '3.0.8.0', 'v3.0.8'));
        $this->repository->addPackage(new Package('test/package', '3.7.2.0', '3.7.2'));

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^3.7.2', false, false, true);
        $this->assertNull($latest); // No downgrade to 3.0.8
    }

    public function test_get_latest_version_with_stability(): void
    {
        $this->repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $this->repository->addPackage(new Package('test/package', '1.0.1-beta.0', '1.0.1-beta')); // Unstable

        $latest = $this->service->getLatestVersion('test/package', 'stable', '^1.0.0', false, false, true);
        $this->assertNull($latest); // No unstable versions with 'stable'

        $latest = $this->service->getLatestVersion('test/package', 'beta', '^1.0.0', false, false, true);
        $this->assertEquals('1.0.1-beta', $latest); // Allows beta with 'beta' stability
    }

    public function test_is_upgrade(): void
    {
        $this->assertTrue($this->service->isUpgrade('1.0.0', '1.0.1'));
        $this->assertFalse($this->service->isUpgrade('1.0.1', '1.0.0')); // Downgrade
        $this->assertFalse($this->service->isUpgrade('1.0.0', '1.0.0')); // Same version
        $this->assertTrue($this->service->isUpgrade('3.7.2', '3.7.3'));
        $this->assertFalse($this->service->isUpgrade('3.7.2', '3.0.8')); // Downgrade
    }

    public function test_get_current_version_from_constraint(): void
    {
        $version = $this->service->getCurrentVersion('test/package', '^1.2.3');
        $this->assertEquals('1.2.3.0', $version); // Extracts base version
    }

    public function test_no_composer_set(): void
    {
        $service = new VersionService(new StabilityChecker());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Composer instance not set.');
        $service->getLatestVersion('test/package', 'stable', '^1.0.0', true, true, true);
    }

    public function test_invalid_constraint_throws_exception(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->service->getCurrentVersion('test/package', 'invalid'); // Invalid constraint
    }

    public function test_empty_repository(): void
    {
        $latest = $this->service->getLatestVersion('test/empty', 'stable', '^1.0.0', true, true, true);
        $this->assertNull($latest); // No packages in repo
    }
}
