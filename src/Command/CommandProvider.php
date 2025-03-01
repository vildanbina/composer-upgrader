<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Command;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Vildanbina\ComposerUpgrader\Service\ComposerFileService;
use Vildanbina\ComposerUpgrader\Service\StabilityChecker;
use Vildanbina\ComposerUpgrader\Service\VersionService;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new UpgradeAllCommand(
                new VersionService(new StabilityChecker()),
                new ComposerFileService()
            ),
        ];
    }
}
