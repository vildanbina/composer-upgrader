<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Service;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Semver\Comparator;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\VersionParser;
use RuntimeException;
use UnexpectedValueException;

class VersionService
{
    private array $versionCache = [];

    private VersionParser $versionParser;

    private StabilityChecker $stabilityChecker;

    private ?Composer $composer = null;

    private ?IOInterface $io = null;

    public function __construct(StabilityChecker $stabilityChecker)
    {
        $this->versionParser = new VersionParser();
        $this->stabilityChecker = $stabilityChecker;
    }

    public function setComposer(Composer $composer): void
    {
        $this->composer = $composer;
    }

    public function setIO(IOInterface $io): void
    {
        $this->io = $io;
    }

    public function getLatestVersion(string $package, string $stability, string $currentConstraint, bool $allowMajor, bool $allowMinor, bool $allowPatch): ?string
    {
        $cacheKey = "$package:$stability:$currentConstraint:$allowMajor:$allowMinor:$allowPatch";
        if (isset($this->versionCache[$cacheKey])) {
            return $this->versionCache[$cacheKey];
        }

        if (! $this->composer) {
            throw new RuntimeException('Composer instance not set.');
        }

        $packages = $this->composer
            ->getRepositoryManager()
            ->findPackages($package, new Constraint('>=', '0.0.0'));

        if (empty($packages)) {
            $this->versionCache[$cacheKey] = null;

            return null;
        }

        try {
            $currentVersion = $this->getCurrentVersion($package, $currentConstraint);
            if ($currentVersion === null) {
                $this->versionCache[$cacheKey] = null;

                return null;
            }
        } catch (UnexpectedValueException $e) {
            if ($this->io && $this->io->isVerbose()) {
                $this->io->writeError("Failed to get current version for $package: ".$e->getMessage());
            }
            $this->versionCache[$cacheKey] = null;

            return null;
        }

        $versions = [];
        foreach ($packages as $pkg) {
            $version = $pkg->getPrettyVersion();
            $pkgStability = $pkg->getStability();

            if ($this->stabilityChecker->isAllowed($pkgStability, $stability)) {
                try {
                    $normalizedVersion = $this->versionParser->normalize($version);
                    if (
                        Comparator::greaterThan($normalizedVersion, $currentVersion) &&
                        $this->isUpgradeAllowed($currentVersion, $normalizedVersion, $allowMajor, $allowMinor, $allowPatch)
                    ) {
                        $versions[$normalizedVersion] = $version;
                        if ($this->io && $this->io->isVerbose()) {
                            $this->io->write("Considering $package: $version");
                        }
                    }
                } catch (UnexpectedValueException $e) {
                    if ($this->io && $this->io->isVerbose()) {
                        $this->io->writeError("Invalid version $version for $package: ".$e->getMessage());
                    }

                    continue;
                }
            }
        }

        if (empty($versions)) {
            $this->versionCache[$cacheKey] = null;

            return null;
        }

        uksort($versions, fn ($a, $b) => Comparator::compare($b, '>', $a) ? 1 : -1);
        $latestVersion = $versions[key($versions)] ?? null;
        $this->versionCache[$cacheKey] = $latestVersion;

        if ($this->io && $this->io->isVerbose() && $latestVersion) {
            $this->io->write(sprintf('Selected latest version for %s: %s (from %s)', $package, $latestVersion, $currentConstraint));
        }

        return $latestVersion;
    }

    public function getCurrentVersion(string $package, string $currentConstraint): ?string
    {
        if ($this->composer && $this->composer->getLocker()->isLocked()) {
            $lockData = $this->composer->getLocker()->getLockData();
            foreach (['packages', 'packages-dev'] as $section) {
                foreach ($lockData[$section] ?? [] as $lockedPackage) {
                    if ($lockedPackage['name'] === $package) {
                        return $this->versionParser->normalize($this->extractBaseVersionFromConstraint($lockedPackage['version']));
                    }
                }
            }
        }

        $version = $this->extractBaseVersionFromConstraint($currentConstraint);

        return $this->versionParser->normalize($version);
    }

    public function isUpgrade(string $currentVersion, string $newVersion): bool
    {
        return Comparator::greaterThan(
            $this->versionParser->normalize($newVersion),
            $this->versionParser->normalize($currentVersion)
        );
    }

    public function extractBaseVersionFromConstraint(string $constraint): string
    {
        preg_match('/(\d+(?:\.\d+){0,2})/', $constraint, $matches);

        return $matches[1] ?? throw new UnexpectedValueException("Unable to extract base version from constraint: $constraint");
    }

    private function isUpgradeAllowed(string $currentVersion, string $newVersion, bool $allowMajor, bool $allowMinor, bool $allowPatch): bool
    {
        $currentParts = explode('.', $currentVersion);
        $newParts = explode('.', $newVersion);

        $majorDiff = (int) $newParts[0] - (int) $currentParts[0];
        $minorDiff = (int) $newParts[1] - (int) $currentParts[1];
        $patchDiff = (int) $newParts[2] - (int) $currentParts[2];

        if ($majorDiff > 0) {
            return $allowMajor;
        }
        if ($minorDiff > 0) {
            return $allowMinor;
        }
        if ($patchDiff > 0) {
            return $allowPatch;
        }

        return false;
    }
}
