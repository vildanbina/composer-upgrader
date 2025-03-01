<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Service;

class StabilityChecker
{
    private array $stabilityOrder = ['dev', 'alpha', 'beta', 'rc', 'stable'];

    public function isAllowed(string $packageStability, string $desiredStability): bool
    {
        $pkgIndex = array_search($packageStability, $this->stabilityOrder, true);
        $desiredIndex = array_search($desiredStability, $this->stabilityOrder, true);

        return $pkgIndex >= $desiredIndex || $desiredStability === 'dev';
    }
}
