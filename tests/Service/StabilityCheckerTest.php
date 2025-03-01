<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Tests\Service;

use PHPUnit\Framework\TestCase;
use Vildanbina\ComposerUpgrader\Service\StabilityChecker;

class StabilityCheckerTest extends TestCase
{
    private StabilityChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new StabilityChecker();
    }

    public function test_stable_allows_stable(): void
    {
        $this->assertTrue($this->checker->isAllowed('stable', 'stable'));
    }

    public function test_dev_allows_all(): void
    {
        $this->assertTrue($this->checker->isAllowed('stable', 'dev'));
        $this->assertTrue($this->checker->isAllowed('beta', 'dev'));
        $this->assertTrue($this->checker->isAllowed('alpha', 'dev'));
    }

    public function test_stable_rejects_unstable(): void
    {
        $this->assertFalse($this->checker->isAllowed('beta', 'stable'));
        $this->assertFalse($this->checker->isAllowed('dev', 'stable'));
    }

    public function test_beta_allows_beta_and_above(): void
    {
        $this->assertTrue($this->checker->isAllowed('beta', 'beta'));
        $this->assertTrue($this->checker->isAllowed('rc', 'beta'));
        $this->assertTrue($this->checker->isAllowed('stable', 'beta'));
        $this->assertFalse($this->checker->isAllowed('alpha', 'beta'));
    }

    public function test_alpha_allows_all_except_dev(): void
    {
        $this->assertTrue($this->checker->isAllowed('alpha', 'alpha'));
        $this->assertTrue($this->checker->isAllowed('beta', 'alpha'));
        $this->assertFalse($this->checker->isAllowed('dev', 'alpha'));
    }
}
