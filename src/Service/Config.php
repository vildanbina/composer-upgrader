<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Service;

use Symfony\Component\Console\Input\InputInterface;

class Config
{
    public bool $dryRun;

    public string $stability;

    public ?array $only;

    public bool $allowMajor;

    public bool $allowMinor;

    public bool $allowPatch;

    public function __construct(InputInterface $input)
    {
        $this->dryRun = (bool) $input->getOption('dry-run');
        $this->stability = (string) $input->getOption('stability');
        $this->only = $input->getOption('only') ? explode(',', $input->getOption('only')) : null;
        $this->allowMajor = (bool) $input->getOption('major');
        $this->allowMinor = (bool) $input->getOption('minor');
        $this->allowPatch = (bool) $input->getOption('patch');

        if (! $this->allowMajor && ! $this->allowMinor && ! $this->allowPatch) {
            $this->allowMajor = true;
            $this->allowMinor = true;
            $this->allowPatch = true;
        }
    }
}
