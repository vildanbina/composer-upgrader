<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Command;

use PHPUnit\Framework\TestCase;
use Vildanbina\ComposerUpgrader\Command\CommandProvider;
use Vildanbina\ComposerUpgrader\Command\UpgradeAllCommand;

class CommandProviderTest extends TestCase
{
    public function test_get_commands(): void
    {
        $provider = new CommandProvider();
        $commands = $provider->getCommands();

        $this->assertCount(1, $commands);
        $this->assertInstanceOf(UpgradeAllCommand::class, $commands[0]);
        $this->assertEquals('upgrade-all', $commands[0]->getName());
    }
}
