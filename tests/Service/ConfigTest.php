<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Vildanbina\ComposerUpgrader\Service\Config;

class ConfigTest extends TestCase
{
    private InputDefinition $definition;

    protected function setUp(): void
    {
        $this->definition = new InputDefinition([
            new InputOption('major', null, InputOption::VALUE_NONE),
            new InputOption('minor', null, InputOption::VALUE_NONE),
            new InputOption('patch', null, InputOption::VALUE_NONE),
            new InputOption('dry-run', null, InputOption::VALUE_NONE),
            new InputOption('stability', null, InputOption::VALUE_REQUIRED, '', 'stable'),
            new InputOption('only', null, InputOption::VALUE_REQUIRED),
        ]);
    }

    public function test_default_config(): void
    {
        $input = new ArrayInput([], $this->definition);
        $config = new Config($input);

        $this->assertFalse($config->dryRun);
        $this->assertEquals('stable', $config->stability);
        $this->assertNull($config->only);
        $this->assertTrue($config->allowMajor);
        $this->assertTrue($config->allowMinor);
        $this->assertTrue($config->allowPatch);
    }

    public function test_minor_only(): void
    {
        $input = new ArrayInput(['--minor' => true], $this->definition);
        $config = new Config($input);

        $this->assertFalse($config->dryRun);
        $this->assertEquals('stable', $config->stability);
        $this->assertNull($config->only);
        $this->assertFalse($config->allowMajor);
        $this->assertTrue($config->allowMinor);
        $this->assertFalse($config->allowPatch);
    }

    public function test_dry_run_and_only(): void
    {
        $input = new ArrayInput(['--dry-run' => true, '--only' => 'package1,package2'], $this->definition);
        $config = new Config($input);

        $this->assertTrue($config->dryRun);
        $this->assertEquals('stable', $config->stability);
        $this->assertEquals(['package1', 'package2'], $config->only);
        $this->assertTrue($config->allowMajor);
        $this->assertTrue($config->allowMinor);
        $this->assertTrue($config->allowPatch);
    }
}
