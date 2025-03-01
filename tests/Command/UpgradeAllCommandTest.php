<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Command;

use Composer\Composer;
use Composer\Console\Application;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Vildanbina\ComposerUpgrader\Command\UpgradeAllCommand;
use Vildanbina\ComposerUpgrader\Service\ComposerFileService;
use Vildanbina\ComposerUpgrader\Service\StabilityChecker;
use Vildanbina\ComposerUpgrader\Service\VersionService;

class UpgradeAllCommandTest extends TestCase
{
    private UpgradeAllCommand $command;

    private ComposerFileService $fileService;

    private VersionService $versionService;

    protected function setUp(): void
    {
        $this->fileService = $this->createMock(ComposerFileService::class);
        $this->versionService = new VersionService(new StabilityChecker());
        $this->command = new UpgradeAllCommand($this->versionService, $this->fileService);

        $composer = $this->createMock(Composer::class);
        $repoManager = $this->createMock(RepositoryManager::class);
        $composer->method('getRepositoryManager')->willReturn($repoManager);

        $repository = new ArrayRepository();
        $repository->addPackage(new Package('test/package', '1.0.0.0', '1.0.0'));
        $repository->addPackage(new Package('test/package', '1.0.1.0', '1.0.1'));
        $repository->addPackage(new Package('test/package', '1.1.0.0', '1.1.0'));
        $repository->addPackage(new Package('test/package', '2.0.0.0', '2.0.0'));
        $repository->addPackage(new Package('test/other', '2.0.0.0', '2.0.0'));
        $repository->addPackage(new Package('test/other', '2.0.1-beta', '2.0.1-beta'));
        $repoManager->method('findPackages')->willReturn($repository->getPackages());

        $locker = $this->createMock(Locker::class);
        $locker->method('isLocked')->willReturn(false);
        $composer->method('getLocker')->willReturn($locker);

        $application = $this->createMock(Application::class);
        $application->method('getComposer')->willReturn($composer);

        $this->command->setApplication($application);
        $this->versionService->setComposer($composer);
    }

    public function test_execute_dry_run(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => ['test/package' => '^1.0.0'],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn(['test/package' => '^1.0.0']);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true, '--patch' => true]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Fetching latest package versions...', $output);
        $this->assertStringContainsString('Found test/package: ^1.0.0 -> 1.0.1', $output);
        $this->assertStringContainsString('Dry run complete. No changes applied.', $output);
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function test_missing_composer_json(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn(null);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Invalid or missing composer.json file.', $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function test_execute_updates_composer_json(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => ['test/package' => '^1.0.0'],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn(['test/package' => '^1.0.0']);
        $this->fileService->expects($this->once())
            ->method('saveComposerJson');

        $tester = new CommandTester($this->command);
        $tester->execute(['--patch' => true]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Fetching latest package versions...', $output);
        $this->assertStringContainsString('Found test/package: ^1.0.0 -> 1.0.1', $output);
        $this->assertStringContainsString('Composer.json has been updated. Please run "composer update" to apply changes.', $output);
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function test_execute_with_only_option(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => [
                    'test/package' => '^1.0.0',
                    'test/other' => '^2.0.0',
                ],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn([
                'test/package' => '^1.0.0',
                'test/other' => '^2.0.0',
            ]);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true, '--patch' => true, '--only' => 'test/package']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Found test/package: ^1.0.0 -> 1.0.1', $output);
        $this->assertStringNotContainsString('test/other', $output);
        $this->assertStringContainsString('Dry run complete. No changes applied.', $output);
    }

    public function test_execute_with_stability_beta(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => ['test/other' => '^2.0.0'],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn(['test/other' => '^2.0.0']);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true, '--patch' => true, '--stability' => 'beta']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Found test/other: ^2.0.0 -> 2.0.1-beta', $output);
    }

    public function test_execute_with_all_flags(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => ['test/package' => '^1.0.0'],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn(['test/package' => '^1.0.0']);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true, '--major' => true, '--minor' => true, '--patch' => true]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Found test/package: ^1.0.0 -> 2.0.0', $output);
    }

    public function test_execute_no_upgrades_available(): void
    {
        $this->fileService->expects($this->once())
            ->method('loadComposerJson')
            ->willReturn([
                'require' => ['test/package' => '^2.0.0'],
            ]);
        $this->fileService->expects($this->once())
            ->method('getDependencies')
            ->willReturn(['test/package' => '^2.0.0']);

        $tester = new CommandTester($this->command);
        $tester->execute(['--dry-run' => true, '--patch' => true]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Fetching latest package versions...', $output);
        $this->assertStringNotContainsString('Found test/package', $output);
        $this->assertStringContainsString('Dry run complete. No changes applied.', $output);
    }
}
