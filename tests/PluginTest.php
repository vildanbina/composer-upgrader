<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests;

use Composer\Plugin\Capability\CommandProvider;
use PHPUnit\Framework\TestCase;
use Vildanbina\ComposerUpgrader\Plugin;

class PluginTest extends TestCase
{
    public function test_get_capabilities(): void
    {
        $plugin = new Plugin();
        $capabilities = $plugin->getCapabilities();

        $this->assertArrayHasKey(CommandProvider::class, $capabilities);
        $this->assertEquals(\Vildanbina\ComposerUpgrader\Command\CommandProvider::class, $capabilities[CommandProvider::class]);
    }

    public function test_activate_deactivate_uninstall(): void
    {
        $plugin = new Plugin();
        $composer = $this->createMock(\Composer\Composer::class);
        $io = $this->createMock(\Composer\IO\IOInterface::class);

        // No exceptions should be thrown
        $plugin->activate($composer, $io);
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);

        $this->assertTrue(true); // Just ensure no errors
    }
}
