<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class Plugin implements Capable, PluginInterface
{
    public function activate(Composer $composer, IOInterface $io) {}

    public function deactivate(Composer $composer, IOInterface $io) {}

    public function uninstall(Composer $composer, IOInterface $io) {}

    public function getCapabilities()
    {
        return [
            CommandProvider::class => Command\CommandProvider::class,
        ];
    }
}
