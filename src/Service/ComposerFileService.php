<?php

declare(strict_types=1);

namespace Vildanbina\ComposerUpgrader\Service;

class ComposerFileService
{
    public function loadComposerJson(string $path): ?array
    {
        if (! file_exists($path)) {
            return null;
        }

        $content = json_decode(file_get_contents($path), true);

        return $content === null ? null : $content;
    }

    public function getDependencies(array $composerJson): array
    {
        $requires = $composerJson['require'] ?? [];
        $requiresDev = $composerJson['require-dev'] ?? [];

        return array_merge($requires, $requiresDev);
    }

    public function updateDependency(array &$composerJson, string $package, string $version): void
    {
        if (isset($composerJson['require'][$package])) {
            $composerJson['require'][$package] = $version;
        } elseif (isset($composerJson['require-dev'][$package])) {
            $composerJson['require-dev'][$package] = $version;
        }
    }

    public function saveComposerJson(array $composerJson, string $path): void
    {
        file_put_contents($path, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
