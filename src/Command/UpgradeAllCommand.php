<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;
use Vildanbina\ComposerUpgrader\Service\ComposerFileService;
use Vildanbina\ComposerUpgrader\Service\Config;
use Vildanbina\ComposerUpgrader\Service\VersionService;

class UpgradeAllCommand extends BaseCommand
{
    private VersionService $versionService;

    private ComposerFileService $composerFileService;

    public function __construct(VersionService $versionService, ComposerFileService $composerFileService)
    {
        $this->versionService = $versionService;
        $this->composerFileService = $composerFileService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('upgrade-all')
            ->setDescription('Upgrade all Composer dependencies to their latest versions.')
            ->addOption('major', null, InputOption::VALUE_NONE, 'Include major version upgrades')
            ->addOption('minor', null, InputOption::VALUE_NONE, 'Include minor version upgrades (default)')
            ->addOption('patch', null, InputOption::VALUE_NONE, 'Include patch version upgrades (default)')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate the upgrade without applying changes')
            ->addOption('stability', null, InputOption::VALUE_REQUIRED, 'Set minimum stability (stable, beta, alpha, dev)', 'stable')
            ->addOption('only', null, InputOption::VALUE_REQUIRED, 'Upgrade only specific packages (comma-separated)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = new Config($input);
        $composer = $this->requireComposer();

        $output->writeln('Fetching latest package versions...');

        $composerJsonPath = getcwd().'/composer.json';
        $composerJson = $this->composerFileService->loadComposerJson($composerJsonPath);
        if ($composerJson === null) {
            $output->writeln('Invalid or missing composer.json file.');

            return 1;
        }

        if (! $config->dryRun && ! file_exists(getcwd().'/composer.lock')) {
            $output->writeln('No composer.lock found. Run "composer install" first.');

            return 1;
        }

        $dependencies = $this->composerFileService->getDependencies($composerJson);

        $this->versionService->setComposer($composer);
        $this->versionService->setIO($this->getIO());

        foreach ($dependencies as $package => $constraint) {
            if ($config->only && ! in_array($package, $config->only)) {
                continue;
            }

            if (preg_match('/^(php|ext-)/', $package)) {
                continue;
            }

            $latestVersion = $this->versionService->getLatestVersion(
                $package,
                $config->stability,
                $constraint,
                $config->allowMajor,
                $config->allowMinor,
                $config->allowPatch
            );

            try {
                $currentVersion = $this->versionService->getCurrentVersion($package, $constraint);
                if ($latestVersion && $this->versionService->isUpgrade($currentVersion, $latestVersion)) {
                    $output->writeln(sprintf('Found %s: %s -> %s', $package, $constraint, $latestVersion));
                    if (! $config->dryRun) {
                        $cleanVersion = preg_replace('/^v/', '', $latestVersion);
                        $this->composerFileService->updateDependency($composerJson, $package, '^'.$cleanVersion);
                    }
                } elseif ($output->isVerbose()) {
                    $output->writeln(sprintf('Skipping %s: %s already satisfies %s', $package, $constraint, $latestVersion ?? 'N/A'));
                }
            } catch (UnexpectedValueException $e) {
                if ($output->isVerbose()) {
                    $output->writeln("<error>Error processing $package: {$e->getMessage()}</error>");
                }
            }
        }

        if (! $config->dryRun) {
            $this->composerFileService->saveComposerJson($composerJson, $composerJsonPath);
            $output->writeln('Composer.json has been updated. Please run "composer update" to apply changes.');
        } else {
            $output->writeln('Dry run complete. No changes applied.');
        }

        return 0;
    }
}
